<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact_contoller')]
    public function index(): Response
    {

        $form = $this->createForm(ContactType::class);
        /*dd('ok'); 
        return $this->render('contact_contoller/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
        */
        // return new Response('ok');
        return $this->render('contact_contoller/index.html.twig', [
            'form' => $form,
        ]);
        return new Response('ok');
    }
    #[Route('/sendEmail', name: 'app_send_email',  methods: ['GET', 'POST'])]
    public function sendEmail(Request $request): Response
    {

        $form = $this->createForm(ContactType::class);
        //  dd($form->handleRequest($request)->all());
        $data=$form->getData();
        dd($data);
        $email = $request->request->get('email');
        $nom = $request->request->get('nom');
        dd($email);


        //$form = $this->createForm(ContactType::class);
        /*dd('ok'); 
        return $this->render('contact_contoller/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
        */
        // return new Response('ok');
        /*return $this->render('contact_contoller/index.html.twig', [
            'form' => $form,
        ]);*/
        return new Response('data');
    }
}
