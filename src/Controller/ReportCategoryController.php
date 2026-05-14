<?php

namespace App\Controller;

use App\Entity\ReportCategory;
use App\Form\ReportCategoryType;
use App\Repository\ReportCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/report/category')]
final class ReportCategoryController extends AbstractController
{
    #[Route(name: 'app_report_category_index', methods: ['GET'])]
    public function index(ReportCategoryRepository $reportCategoryRepository): Response
    {
        return $this->render('report_category/index.html.twig', [
            'report_categories' => $reportCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_report_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reportCategory = new ReportCategory();
        $form = $this->createForm(ReportCategoryType::class, $reportCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reportCategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_report_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('report_category/new.html.twig', [
            'report_category' => $reportCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_report_category_show', methods: ['GET'])]
    public function show(ReportCategory $reportCategory): Response
    {
        return $this->render('report_category/show.html.twig', [
            'report_category' => $reportCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_report_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReportCategory $reportCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReportCategoryType::class, $reportCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_report_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('report_category/edit.html.twig', [
            'report_category' => $reportCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_report_category_delete', methods: ['POST'])]
    public function delete(Request $request, ReportCategory $reportCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reportCategory->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reportCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_report_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
