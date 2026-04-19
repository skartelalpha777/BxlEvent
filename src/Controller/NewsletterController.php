<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Form\NewsletterType;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/newsletter')]
final class NewsletterController extends AbstractController
{
    #[Route(name: 'app_newsletter_index', methods: ['GET'])]
    public function index(NewsletterRepository $newsletterRepository): Response
    {
        return $this->render('newsletter/index.html.twig', [
            'newsletters' => $newsletterRepository->findAll(),
        ]);
    }

    /*
    Permet l'inscription à la newsletter
    depuis la page d'accueil
    */
    #[Route('/new', name: 'app_newSuscrib_new', methods: ['GET', 'POST'])]
    public function newSuscrib(Request $request, EntityManagerInterface $entityManager): Response
    {
       $email = $request->request->get('email');
    $token = $request->request->get('_token');

    if (!$this->isCsrfTokenValid('newsletter_token', $token)) {
        throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
    }

    if (!$email) {
        $this->addFlash('error', "Veuillez renseigner une adresse email.");
        return $this->redirectToRoute('app_event_index');
    }

    $alreadyExists = $entityManager->getRepository(Newsletter::class)->findOneBy(['email' => $email]);

    if ($alreadyExists) {
        $this->addFlash('error', 'Vous êtes déjà inscrit à la newsletter.');
        return $this->redirectToRoute('app_event_index');
    }

    $newsletter = new Newsletter();
    $newsletter->setEmail($email);
    $newsletter->setInscriptionDate(new \DateTime());

    $entityManager->persist($newsletter);
    $entityManager->flush();

    $this->addFlash('success', 'Inscription réussie !');
    
    return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);

    }








    #[Route('/new', name: 'app_newsletter_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $newsletter = new Newsletter();
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($newsletter);
            $entityManager->flush();

            return $this->redirectToRoute('app_newsletter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('newsletter/new.html.twig', [
            'newsletter' => $newsletter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_newsletter_show', methods: ['GET'])]
    public function show(Newsletter $newsletter): Response
    {
        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_newsletter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Newsletter $newsletter, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_newsletter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('newsletter/edit.html.twig', [
            'newsletter' => $newsletter,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_newsletter_delete', methods: ['POST'])]
    public function delete(Request $request, Newsletter $newsletter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $newsletter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($newsletter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_newsletter_index', [], Response::HTTP_SEE_OTHER);
    }
}
