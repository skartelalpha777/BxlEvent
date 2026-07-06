<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Enum\OrderStatus;
use App\Form\TicketFormType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TicketTypeRepository;
use App\Repository\OrderRepository;
use App\Entity\Order;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;



#[Route('/ticket')]
final class TicketController extends AbstractController
{
    #[Route(name: 'app_ticket_index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository): Response
    {
        return $this->render('ticket/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
        ]);
    }

    #[Route('/newTicket', name: 'app_ticket_newTicket', methods: ['POST'])]
    public function newTicket(
        Request $request,
        EntityManagerInterface $entityManager,
        TicketTypeRepository $ticketTypeRepository,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $allTicket = $request->request->all('tickets');
        $sendedToken = $request->request->get('_token');
        
    

        if (!$this->isCsrfTokenValid('_token', $sendedToken)) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('app_event_index'); // Adaptez la redirection
        }

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter des tickets.');
            return $this->redirectToRoute('app_login');
        }


        $order = new Order();
        $order->setUser($user);
        $order->setStatus(OrderStatus::Pending);

        $totalPrice = 0;
        $lineItems = [];
        $cost = 2.50;

        foreach ($allTicket as $ticketTypeId => $quantity) {
            $quantity = (int) $quantity;

            if ($quantity > 0) {
                $ticketType = $ticketTypeRepository->find($ticketTypeId);

                if ($ticketType) {
                    $price =  $ticketType->getPrice();
                    $totalPrice = $totalPrice +  ($price * $quantity);

                    // Préparation de l'article pour Stripe Checkout
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $ticketType->getLabel()->value . ' - ' . $ticketType->getEvent()->getTitle(),
                            ],
                            'unit_amount' => $price * 100, // Stripe utilise des centimes (ex: 10€ = 1000)
                        ],
                        'quantity' => $quantity,

                    ];

                    // Création de N tickets pour ce type
                    for ($i = 0; $i < $quantity; $i++) {
                        $ticket = new Ticket();
                        $ticket->setTicketType($ticketType);
                        $ticket->setEvent($ticketType->getEvent());
                        $ticket->setUser($user);
                        $ticket->setPurchase($order);
                        $ticket->setDate(new \DateTime());

                        // Génération d'un code unique qui servira pour le QR code
                        $uniqueCode = uniqid('TICKET_' . $ticketType->getId() . '_', true);
                        $ticket->setCode($uniqueCode);
                        $ticket->setIsScanned(false);

                        $entityManager->persist($ticket);
                    }
                }
            }
        }
        if ($totalPrice >= 2.50) {
            $lineItems[0]['price_data']['unit_amount'] += 250;
        }
        
        if ($totalPrice < 2.50) {
            $this->addFlash('error', 'Veuillez sélectionner au moins un ticket.');
            return $this->redirectToRoute('app_event_consult'); // Adaptez la redirection
        }

        $order->setTotalPrice((string) $totalPrice);
        $entityManager->persist($order);

        // Sauvegarde en Base de données
        $entityManager->flush();

        // --------------------------------------------------------
        // INTEGRATION STRIPE
        // --------------------------------------------------------
        // Mettez cette clé dans le fichier .env (STRIPE_SECRET_KEY=sk_test_...)
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'] ;
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $checkout_session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'line_items' => $lineItems,

            // On passe la référence de l'Order dans l'URL pour la récupérer après le paiement
            'success_url' => $urlGenerator->generate('app_payment_success', ['reference' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $urlGenerator->generate('app_payment_cancel', ['reference' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Redirection de l'utilisateur vers la page de paiement Stripe
        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/payment/success/{reference}', name: 'app_payment_success', methods: ['GET'])]
    public function paymentSuccess(string $reference, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneBy(['reference' => $reference]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $order->setStatus(OrderStatus::Paid);
        $entityManager->flush();

        return $this->render('ticket/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/payment/cancel/{reference}', name: 'app_payment_cancel', methods: ['GET'])]
    public function paymentCancel(string $reference, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneBy(['reference' => $reference]);
        if ($order && $order->getUser() === $this->getUser()) {
            $order->setStatus(OrderStatus::Cancelled);
            $entityManager->flush();
        }

        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_home_event_index');
    }

    #[Route('/download/tickets/{reference}', name: 'app_ticket_download', methods: ['GET'])]
    public function downloadTickets(string $reference, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->findOneBy(['reference' => $reference]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $writer = new PngWriter();
        $ticketsData = [];

        // Pour chaque ticket dans l'order, on génère un QR code encodé en Base64
        foreach ($order->getTickets() as $ticket) {
            $qrCode = new QrCode(
                data: 'Life is too short to be generating QR codes',
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Low,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,

            );
            //dd($qrCode);


            // $qrCode = QrCode::create($ticket->getCode());
            $result = $writer->write($qrCode);

            $ticketsData[] = [
                'ticket' => $ticket,
                'qrBase64' => base64_encode($result->getString()), // Base64 permet d'intégrer l'image direct dans le HTML
            ];
        }

        // On envoie ces données à un template Twig qui fera le design du PDF
        $html = $this->renderView('ticket/pdf.html.twig', [
            'ticketsData' => $ticketsData,
            'order' => $order
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // Autorise le chargement d'images via URL HTTP

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourne le fichier PDF au navigateur pour le téléchargement
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="billets_' . $order->getReference() . '.pdf"'
        ]);
    }

    #[Route('/new', name: 'app_ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
