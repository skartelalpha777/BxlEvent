<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact_contoller', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $nom = $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();
            $emailAddress = $form->get('email')->getData();
            $message = $form->get('message')->getData();
            $email = (new Email())
                ->from($emailAddress)
                ->to('3010madiallo@student.epfc.eu')
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject($nom . ' ' . $prenom)
                ->text($message);
            //->html('<p>See Twig integration for better HTML integration!</p>');

            $mailer->send($email);
            $this->addFlash('success', 'Votre message à bien été envoyé. Nous réviendrons vers vous le plus rapidement possible');

            return  $this->redirectToRoute('app_contact_contoller');
        }


        return $this->render('contact_contoller/index.html.twig', [
            'form' => $form,
        ]);
    }
}
