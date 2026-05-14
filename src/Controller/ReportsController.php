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
use App\Enum\UserRole;


#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(name: 'app_reports_index', methods: ['GET'])]
    public function index(ReportsRepository $reportsRepository): Response
    {
        return $this->render('reports/index.html.twig', [
            'reports' => $reportsRepository->findAll(),
        ]);
    }
  // a coriger  #[IsGranted(UserRole::MEMBRE ,UserRole::CONTRIBUTEUR, UserRole::ADMIN)]
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
  // a corriger  #[IsGranted(UserRole::ADMIN, UserRole::CONTRIBUTEUR)]
    #[Route('/{id}', name: 'app_reports_show', methods: ['GET'])]
    public function show(Reports $report): Response
    {
        return $this->render('reports/show.html.twig', [
            'report' => $report,
        ]);
    }
  //a coriger  #[IsGranted(UserRole::ADMIN, UserRole::CONTRIBUTEUR)]
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

    #[Route('/{id}', name: 'app_reports_delete', methods: ['POST'])]
    public function delete(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $report->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($report);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
    }
}
