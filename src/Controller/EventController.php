<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategorieRepository;
use App\Repository\LocationRepository;
use App\Repository\ReportsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, CategorieRepository $categorieRepository, LocationRepository $locationRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $events = $eventRepository->findAll();

        if ($request->query->count() > 0) {
            $submittedToken = $request->query->get('filter_token');
            if ($this->isCsrfTokenValid('filter', $submittedToken)) {

                $search = $request->query->get('search') ?: null;

                $category = null;
                if ($categoryId = $request->query->get('category')) {
                    $category = $categorieRepository->find($categoryId);
                }

                $date = null;
                if ($dateParam = $request->query->get('date')) {
                    $date = new \DateTime($dateParam);
                }

                $location = null;
                if ($locationId = $request->query->get('location')) {
                    $location = $locationRepository->find($locationId);
                }

                $sort = $request->query->get('sort') === 'DESC' ? 'DESC' : 'ASC';

                $events = $eventRepository->findByFilters($search, $category, $date, $location, $sort);
            }
        }
        $events = $paginator->paginate(
            $events,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'categories' => $categorieRepository->findAll(),
            'locations' => $locationRepository->findAll(),
        ]);
    }
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/mes-evenements', name: 'app-mes-evenements', methods: ['GET'])]
    public function myEvents(EventRepository $eventRepository, ReportsRepository $reportsRepository): Response
    {
        $user = $this->getUser();
        $events = $eventRepository->findBy(['creator' => $user->getId()], ['date' => 'DESC']);

        $revenu = 0;
        $totalTickets = 0;
        $upcoming = 0;
        $past = 0;
        $now = new \DateTime();
        foreach ($events as $event) {
            $revenu += $eventRepository->getEventRevenu($event->getId());
            $totalTickets += $eventRepository->getEventTotalTickest($event->getId());
            if ($event->getDate() >= $now) {
                $upcoming++;
            } else {
                $past++;
            }
        }

        return $this->render('event/dashboard.html.twig', [
            'myEvents' => $events,
            'revenu' => $revenu,
            'totalTickets' => $totalTickets,
            'upcomingCount' => $upcoming,
            'pastCount' => $past,
            'reportsCount' => $reportsRepository->countForCreator($user),
        ]);
    }

    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/mes-statistiques', name: 'app-mes-statistiques', methods: ['GET'])]
    public function myStatistics(EventRepository $eventRepository, ChartBuilderInterface $chartBuilder): Response
    {

        $chart = $this->SaledTickest($eventRepository,  $chartBuilder);

        return $this->render('event/statistiques.html.twig', [
            'chart' => $chart
        ]);
    }

    private function SaledTickest(EventRepository $eventRepository, ChartBuilderInterface $chartBuilder)
    {
        $sales = $eventRepository->getTicketsAndRevenuByDay(null, null, $this->getUser()->getId());

        $labels = [];
        $data = [];
        $backgorungColors = [];
        foreach ($sales as $sale) {
            $date = $sale['day'];
            $labels[] =  $date->format('d-m-y');
            $data[] = $sale['tickets'];
            $backgorungColors[] = sprintf('#%06X', random_int(0, 0xFFFFFF));
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tickets vendus',
                    'backgroundColor' => $backgorungColors,
                    'hoverOffset' => 4,
                    'data' => $data
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                        'wheel' => ['enabled' => true],
                        'pinch' => ['enabled' => true],
                        'mode' => 'xy',
                    ],
                    'pan' => [
                        'enabled' => true,
                        'mode' => 'xy',
                    ],
                ]
            ]
        ]);
        return $chart;
    }
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{id}/statistiques', name: 'app-event-stat', methods: ['GET'])]
    public function eventStat(int $id, EventRepository $eventRepository)
    {
        $totalTickets = $eventRepository->getEventTotalTickest($id);
        $this->render('event/dashboard.html.twig', [
            'totalTickets' => $totalTickets,
        ]);
    }

    #[Route('/featured', name: 'app_events_featured', methods: ['GET'])]
    public function featuredEvents(EventRepository $eventRepository): Response
    {

        $events = $eventRepository->findBy(['isFeatured' => 1]);
        return $this->render('event/featured.html.twig', [
            'events' => $events
        ]);
    }

    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }



    #[Route('/{id}/consult', name: 'app_event_consult', methods: ['GET'])]
    public function consult(Event $event, EventRepository $eventRepository): Response
    {

        return $this->render('event/consult.html.twig', [
            'event' => $event,
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/{id}/tickets', name: 'app_event_tickets', methods: ['GET'])]
    public function tickets(Event $event, EventRepository $eventRepository): Response
    {

        return $this->render('event/tickets.html.twig', [
            'event' => $event,
            'events' => $eventRepository->findAll(),
        ]);
    }





    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
