<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Form\TicketFormType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
use App\Service\CartService;



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

    #[Route('/newTicket', name: 'app_ticket_newTicket', methods: ['POST', 'GET'])]
    public function newTicket(
        UrlGeneratorInterface $urlGenerator,
        CartService $cartService,
        EntityManagerInterface $entityManager
    ): Response {
        $cartItems = $cartService->getCart();
        if (empty($cartItems)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour acheter des tickets.');
            return $this->redirectToRoute('app_login');
        }

        // Créer la commande en base de données
        $order = $this->createPendingOrder($user, $cartService, $entityManager);

        //  Préparer les articles pour Stripe et créer les entités Ticket
        $lineItems = $this->prepareLineItemsAndTickets($cartItems, $order, $user, $entityManager);

        //  Sauvegarder les nouveaux tickets et la commande mise à jour
        $entityManager->flush();

        // Créer la session de paiement Stripe
        $checkout_session = $this->createStripeCheckoutSession($lineItems, $order, $urlGenerator);

        return $this->redirect($checkout_session->url, 303);
    }

    /**
     * gere le cas d'un payement avec succes et reference   est l'identifiant 
     * unique de la commande qui a été créée juste avant le paiement.
     */
    #[Route('/payment/success/{reference}', name: 'app_payment_success', methods: ['GET'])]
    public function paymentSuccess(
        string $reference,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        CartService $cartService
    ): Response {
        $order = $orderRepository->findOneBy(['reference' => $reference]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $order->setStatus(OrderStatus::Paid);
        $entityManager->flush();
        $cartService->clearCart();
        return $this->render('ticket/success.html.twig', [
            'order' => $order,
        ]);
    }
    //gere le cas d'un payement avec echec et reference est  est l'identifiant 
    //unique de la commande qui a été créée juste avant le paiement. 
    #[Route('/payment/cancel/{reference}', name: 'app_payment_cancel', methods: ['GET'])]
    public function paymentCancel(string $reference, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->findOneBy(['reference' => $reference]);
        if ($order && $order->getUser() === $this->getUser()) {
            $order->setStatus(OrderStatus::Cancelled);
            $entityManager->flush();
        }

        $this->addFlash('notice', 'Le paiement a été annulé.');
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

        // Pour chaque ticket dans l'order, on génère un QR code
        foreach ($order->getTickets() as $ticket) {
            $qrCode = new QrCode(
                data: 'QR codes',
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

        //render est utilisé ici seuelement pour générer le HTML du PDF. 
        $html = $this->renderView('ticket/pdf.html.twig', [
            'ticketsData' => $ticketsData,
            'order' => $order
        ]);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); // -> Autorise le chargement d'images via URL HTTP
        // Autorise l'accès au dossier public-> ce qui permet d'acceder a l'image de 
        // l'évnèment pour l'afficher dans le ticket
        $pdfOptions->setChroot([
            $this->getParameter('kernel.project_dir') . '/public',
        ]);

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

    /**
     * Crée et persiste une commande en attente pour l'utilisateur connecté.
     */
    private function createPendingOrder(User $user, CartService $cartService, EntityManagerInterface $entityManager): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setStatus(OrderStatus::Pending);
        $order->setTotalPrice((string)($cartService->getTotal() + $cartService->getServicefee()));

        $entityManager->persist($order);

        return $order;
    }



    /**
     * Prépare les line_items pour Stripe et crée les entités Ticket associées.
     */
    private function prepareLineItemsAndTickets(array $cartItems, Order $order, User $user, EntityManagerInterface $entityManager): array
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            $ticketType = $item['ticketType'];
            $quantity = $item['quantity'];

            if ($quantity <= 0) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $ticketType->getLabel()->value . ' - ' . $ticketType->getEvent()->getTitle(),
                    ],
                    'unit_amount' => $ticketType->getPrice() * 100,
                ],
                'quantity' => $quantity,
            ];

            for ($i = 0; $i < $quantity; $i++) {
                $ticket = new Ticket();
                $ticket->setTicketType($ticketType);
                $ticket->setEvent($ticketType->getEvent());
                $ticket->setUser($user);
                $ticket->setPurchase($order);
                $ticket->setDate(new \DateTime());
                $ticket->setCode(uniqid('TICKET_' . $ticketType->getId() . '_', true));
                $ticket->setIsScanned(false);

                $entityManager->persist($ticket);
            }
        }
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Frais de service',
                ],
                'unit_amount' => 2 * 100,
            ],
            'quantity' => $quantity,
        ];
        return $lineItems;
    }

    /**
     * Initialise et retourne une session de paiement Stripe.
     */
    private function createStripeCheckoutSession(array $lineItems, Order $order, UrlGeneratorInterface $urlGenerator): \Stripe\Checkout\Session
    {
        $stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        return \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $urlGenerator->generate(
                'app_payment_success',
                ['reference' => $order->getReference()],
                UrlGeneratorInterface::ABSOLUTE_URL // génère l'url absolue complete ainsi que le protocol
            ),
            'cancel_url' => $urlGenerator->generate(
                'app_payment_cancel',
                ['reference' => $order->getReference()],
                UrlGeneratorInterface::ABSOLUTE_URL // génère l'url absolue complete ainsi que le protocol
            ),
        ]);
    }
}
