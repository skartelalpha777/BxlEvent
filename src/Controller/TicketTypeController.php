<?php

namespace App\Controller;

use App\Entity\TicketType;
use App\Form\TicketTypeType;
use App\Repository\TicketTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ticket/type')]
final class TicketTypeController extends AbstractController
{
    #[Route(name: 'app_ticket_type_index', methods: ['GET'])]
    public function index(TicketTypeRepository $ticketTypeRepository): Response
    {
        return $this->render('ticket_type/index.html.twig', [
            'ticket_types' => $ticketTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ticket_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ticketType = new TicketType();
        $form = $this->createForm(TicketTypeType::class, $ticketType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ticketType);
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket_type/new.html.twig', [
            'ticket_type' => $ticketType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_type_show', methods: ['GET'])]
    public function show(TicketType $ticketType): Response
    {
        return $this->render('ticket_type/show.html.twig', [
            'ticket_type' => $ticketType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ticket_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TicketType $ticketType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketTypeType::class, $ticketType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket_type/edit.html.twig', [
            'ticket_type' => $ticketType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ticket_type_delete', methods: ['POST'])]
    public function delete(Request $request, TicketType $ticketType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticketType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ticketType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ticket_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
