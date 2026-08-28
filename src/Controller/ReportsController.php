<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reports;
use App\Form\ReportsType;
use App\Repository\ReportsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route(name: 'app_reports_index', methods: ['GET'])]
    public function index(ReportsRepository $reportsRepository): Response
    {
        $reports = $reportsRepository->findAll();
        if (!$this->isGranted('ROLE_ADMIN')) {
            $reports = $reportsRepository->findByCreator($this->getUser());
        }
        return $this->render('reports/index.html.twig', [
            'reports' => $reports,
        ]);
    }
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/new/{id}', name: 'app_reports_new', methods: ['GET', 'POST'])]
    public function new(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $report = new Reports();
        $report->setEvent($event);
        $report->setUser($this->getUser());
        $form = $this->createForm(ReportsType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($report);
            $entityManager->flush();
            return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reports/new.html.twig', [
            'report' => $report,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{id}', name: 'app_reports_show', methods: ['GET'])]
    public function show(Reports $report): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $report->getEvent()->getCreator() != $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas afficher le signalement d\'un évènement dont vous n\'êtes pas le titulaire ');
            return $this->redirectToRoute('app_user_profil', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reports/show.html.twig', [
            'report' => $report,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_reports_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReportsType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reports/edit.html.twig', [
            'report' => $report,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{id}/set-treated', name: 'app_reports_toggle_treated', methods: ['POST'])]
    public function setTreated(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        if ($report->getEvent()->getCreator() != $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier le signalement d\'un évènement dont vous n\'êtes pas le titulaire ');
            return $this->redirectToRoute('app_user_profil', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('toggle-treated' . $report->getId(), $request->getPayload()->getString('_token'))) {
            $report->setTreated(!$report->isTreated());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/delete', name: 'app_reports_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $report->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($report);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
    }
}
