<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Event;
use App\Entity\Gallery;
use App\Entity\Location;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategorieRepository;
use App\Repository\LocationRepository;
use App\Repository\ReportsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Enum\Status;
use App\Service\FileUploader;

#[Route('/event')]
final class EventController extends AbstractController
{
    /**
     * Renvoie la liste des évènements à afficher dans l'accueil avec un maximum de 15 par page
     * cette fonction permet également le filtrage des évènements par catégorie, lieux et autre.
     */
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
    /**
     * Permet d'obtenir les informations tel que le revenu, les tickets vendu, les évènements passé et futur 
     * lié à un contributeur
     */
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
        $refused = 0;
        $now = new \DateTime();
        foreach ($events as $event) {
            $revenu += $eventRepository->getEventRevenu($event->getId());
            $totalTickets += $eventRepository->getEventTotalTickest($event->getId());
            if ($event->getDate() >= $now) {
                $upcoming++;
            } else {
                $past++;
            }
            if ($event->getStatus()->value == "refused") {
                $refused++;
            }
        }

        return $this->render('event/dashboard.html.twig', [
            'myEvents' => $events,
            'revenu' => $revenu,
            'totalTickets' => $totalTickets,
            'upcomingCount' => $upcoming,
            'pastCount' => $past,
            'refused' => $refused,
            'reportsCount' => $reportsRepository->countForCreator($user),
        ]);
    }
    /**
     * return un graphique sur l'ensemble du revenu généré par tous les évènements d'un contributeur ainsi qu'un graphique sur le nombre
     * de billet total vendu
     */
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/mes-statistiques', name: 'app-mes-statistiques', methods: ['GET'])]
    public function myStatistics(EventRepository $eventRepository, Request $request, ChartBuilderInterface $chartBuilder): Response
    {
        $sales = $this->getFilteredSales($eventRepository, $request);
        $ticketsChart = $this->buildDayChart($sales, 'tickets', 'Tickets vendus', Chart::TYPE_BAR, $chartBuilder);
        $revenuChart = $this->buildDayChart($sales, 'revenu', 'Chiffre d\'affaire en €', Chart::TYPE_BAR, $chartBuilder);
        $events = $eventRepository->findBy(['creator' => $this->getUser()->getId()]);
        $eventRows = [];
        foreach ($events as $event) {
            $eventRows[] = [
                'title' => $event->getTitle(),
                'revenu' => $eventRepository->getEventRevenu($event->getId()),
                'tickets' => $eventRepository->getEventTotalTickest($event->getId()),
            ];
        }

        $revenuePerEventChart = $this->buildEventRevenuTicketsBarChart($eventRows, 'revenu', 'CA par événement', '#8b5cf6', $chartBuilder);
        $ticketsPerEventChart = $this->buildEventRevenuTicketsBarChart($eventRows, 'tickets', 'Billets vendus par événement', '#3b82f6', $chartBuilder);

        $ticketTypeRows = [];
        foreach ($eventRepository->getTicketsByType($this->getUser()->getId()) as $row) {
            $ticketTypeRows[] = ['label' => $row['label'], 'total' => $row['total']];
        }
        $ticketTypeDistribution = $this->buildDongnhutChart($ticketTypeRows, $chartBuilder);
        $statusLabels = [
            Status::VALIDATED->value => 'Validés',
            Status::REFUSED->value => 'Refusés',
            Status::NOTCHECKED->value => 'En attente',
        ];
        $eventStatusRows = [];
        foreach ($eventRepository->countEventsByStatus($this->getUser()->getId()) as $row) {
            $eventStatusRows[] = ['label' => $statusLabels[$row['status']->value], 'total' => $row['total']];
        }
        $eventStatusDistribution = $this->buildDongnhutChart($eventStatusRows, $chartBuilder);

        return $this->render('event/statistiques.html.twig', [
            'ticketsChart' => $ticketsChart,
            'revenuChart' => $revenuChart,
            'revenuePerEventChart' => $revenuePerEventChart,
            'ticketsPerEventChart' => $ticketsPerEventChart,
            'ticketTypeChart' => $ticketTypeDistribution['chart'],
            'ticketTypeLegend' => $ticketTypeDistribution['legend'],
            'ticketTypeTotal' => $ticketTypeDistribution['total'],
            'eventStatusChart' => $eventStatusDistribution['chart'],
            'eventStatusLegend' => $eventStatusDistribution['legend'],
            'eventStatusTotal' => $eventStatusDistribution['total'],
        ]);
    }

    /**
     * Statistiques détaillées d'un seul événement (répartition par type de billet,
     * ventes et revenu par jour), avec le même filtre de dates que /mes-statistiques.
     */
    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{eventId}/event-statistiques', name: 'app-event-statistiques', methods: ['GET'])]
    public function eventStats(EventRepository $eventRepository, Request $request, ChartBuilderInterface $chartBuilder, int $eventId): Response
    {
        $event = $eventRepository->find($eventId);
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        if ($event->getCreator() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas consulter les statistiques d\'un événement dont vous n\'êtes pas le créateur.');
        }

        $sales = $this->getFilteredSalesForEvent($eventRepository, $request, $eventId);
        $ticketsChart = $this->buildDayChart($sales, 'tickets', 'Tickets vendus', Chart::TYPE_BAR, $chartBuilder);
        $revenuChart = $this->buildDayChart($sales, 'revenu', 'Chiffre d\'affaire en €', Chart::TYPE_BAR, $chartBuilder);

        $ticketTypeRows = [];
        foreach ($eventRepository->getTicketsByTypeForEvent($eventId) as $row) {
            $ticketTypeRows[] = ['label' => $row['label'], 'total' => $row['total']];
        }
        $ticketTypeDistribution = $this->buildDongnhutChart($ticketTypeRows, $chartBuilder);

        $totalTickets = $eventRepository->getEventTotalTickest($eventId);
        $totalRevenue = $eventRepository->getEventRevenu($eventId);
        $capacity = $eventRepository->getEventCapacity($eventId);

        if ($capacity === null) {
            $fillRate = null;
            $fillRateDistribution = null;
        } else {
            $fillRate = $capacity > 0 ? min(100,  round($totalTickets / $capacity * 100)) : 0;
            $fillRateDistribution = $this->buildDongnhutChart([
                ['label' => 'Vendus', 'total' => min($totalTickets, $capacity)],
                ['label' => 'Restants', 'total' => max($capacity - $totalTickets, 0)],
            ], $chartBuilder);
        }

        return $this->render('event/event-statistiques.html.twig', [
            'event' => $event,
            'ticketsChart' => $ticketsChart,
            'revenuChart' => $revenuChart,
            'ticketTypeChart' => $ticketTypeDistribution['chart'],
            'ticketTypeLegend' => $ticketTypeDistribution['legend'],
            'ticketTypeTotal' => $ticketTypeDistribution['total'],
            'totalTickets' => $totalTickets,
            'totalRevenue' => $totalRevenue,
            'capacity' => $capacity,
            'fillRate' => $fillRate,
            'fillRateChart' => $fillRateDistribution['chart'] ?? null,
        ]);
    }

    /**
     * Récupère les ventes par jour, filtrées sur la période soumise si le formulaire a été validé.
     */
    private function getFilteredSales(EventRepository $eventRepository, Request $request): array
    {
        $userId = $this->getUser()->getId();
        $submittedToken = $request->query->get('filter_token');

        if ($this->isCsrfTokenValid('filter', $submittedToken)) {
            $start = new \DateTime($request->query->get('start-date'));
            $end = new \DateTime($request->query->get('end-date'));
            return $eventRepository->getTicketsAndRevenuByDay($start, $end, $userId);
        }
        return $eventRepository->getTicketsAndRevenuByDay(null, null, $userId);
    }
    /**
     * Récupère les ventes par jour d'un seul événement, filtrées sur la période soumise
     * si le formulaire a été validé.
     */
    private function getFilteredSalesForEvent(EventRepository $eventRepository, Request $request, int $eventId): array
    {
        $submittedToken = $request->query->get('filter_token');

        if ($this->isCsrfTokenValid('filter', $submittedToken)) {
            $start = new \DateTime($request->query->get('start-date'));
            $end = new \DateTime($request->query->get('end-date'));
            return $eventRepository->getTicketsAndRevenuByDayForEvent($start, $end, $eventId);
        }

        return $eventRepository->getTicketsAndRevenuByDayForEvent(null, null, $eventId);
    }

    /**
     * Construit un graphique "par jour" à partir des ventes, en lisant la clé demandée ('tickets' ou 'revenu').
     */
    private function buildDayChart(array $sales, string $dataKey, string $label, string $chartType, ChartBuilderInterface $chartBuilder): Chart
    {
        $labels = [];
        $data = [];
        $backgroundColors = [];
        foreach ($sales as $sale) {
            $labels[] = $sale['day']->format('d-m-y');
            $data[] = $sale[$dataKey];
            $backgroundColors[] = sprintf('#%06X', random_int(0, 0xFFFFFF));
        }

        $chart = $chartBuilder->createChart($chartType);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $backgroundColors,
                    'hoverOffset' => 4,
                    'data' => $data,
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'ticks' => ['color' => 'rgba(255, 255, 255, 0.7)'],
                ],
                'x' => [
                    'ticks' => ['color' => 'rgba(255, 255, 255, 0.7)'],
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

    /**
     * Construit un graphique en barres verticales comparant tous les événements sur la valeur
     * demandée ('revenu' ou 'tickets'), dans l'ordre où ils sont fournis. Pas de limite de nombre.
     * @param array $eventRows représente le tableau des label et leur valeur
     * @param string $dataKey  revenu ou tickets permet de determiner le type de graphique a construire
     * @param string $label représente le titre du graphique
     * @param string $color représente la couleur des bar
     * @return Chart un graphique
     * 
     */
    private function buildEventRevenuTicketsBarChart(array $eventRows, string $dataKey, string $label, string $color, ChartBuilderInterface $chartBuilder): Chart
    {
        $titles = array_column($eventRows, 'title');
        $values = array_column($eventRows, $dataKey); // array_column() récupère une seule colonne (ex. 'revenu') de chaque événement
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $titles,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $color,
                    'data' => $values,
                ],
            ],
        ]);
        $chart->setOptions([
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'ticks' => ['color' => 'rgba(255, 255, 255, 0.7)'],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['color' => 'rgba(255, 255, 255, 0.7)'],
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Construit un donut ainsi que les données de légende (couleur, total, %) associées,
     * à partir de lignes {label, total}.
     * @param array $rows réprésente le tabelau de label et les valeur qui  vont avec 
     * @param ChartBuilderInterface $chartBuilder l'interface pour construire le graphique
     * @return array un tableau contenant le grahique, la légende et le nombre total par rapport a la répartition du graphique

     */
    private function buildDongnhutChart(array $rows, ChartBuilderInterface $chartBuilder): array
    {
        /* ici row peut valoir à ça par exemple
         $rows = [
        ['label' => 'Standard', 'total' => 320],
        ['label' => 'Vip', 'total' => 45],
        ['label' => 'Promo', 'total' => 12],
        ]; */
        /* array_sum()
        Additionne les valeurs :
        $tab = [10, 20, 30, 40];
        array_sum($tab); // 100
        */
        $total = array_sum(array_column($rows, 'total'));
        $legend = [];
        foreach ($rows as $row) {
            $percentage = $total > 0 ? round($row['total'] / $total * 100) : 0;
            $legend[] = [
                'label' => $row['label'],
                'total' => $row['total'],
                'percentage' => $percentage,
                'color' => sprintf('#%06X', random_int(0, 0xFFFFFF)),
            ];
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => array_column($legend, 'label'),
            'datasets' => [
                [
                    'backgroundColor' => array_column($legend, 'color'),
                    'data' => array_column($legend, 'total'),
                ],
            ],
        ]);
        $chart->setOptions([
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ]);

        return ['chart' => $chart, 'legend' => $legend, 'total' => $total];
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
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Aucun lieu existant sélectionné : on en crée un nouveau à partir des champs dédiés.
            if (!$event->getLocation() && $form->get('newLocationName')->getData()) {
                $location = new Location();
                $location->setName($form->get('newLocationName')->getData());
                $location->setStreet($form->get('newLocationStreet')->getData());
                $location->setNumber((int) $form->get('newLocationNumber')->getData());
                $location->setPostcode((int) $form->get('newLocationPostcode')->getData());
                $location->setCity($form->get('newLocationCity')->getData());
                $entityManager->persist($location);
                $event->setLocation($location);
            }

            if (!$event->getLocation()) {
                $form->get('location')->addError(new FormError('Choisissez un lieu existant ou renseignez les informations du nouveau lieu.'));
            }

            // Une catégorie qui n'existe pas encore a été saisie : on la crée à la volée.
            $newCategoryName = $form->get('newCategoryName')->getData();
            if ($newCategoryName) {
                $category = new Categorie();
                $category->setName($newCategoryName);
                $entityManager->persist($category);
                $event->addCategory($category);
            }

            if ($event->getLocation()) {
                $event->setCreator($this->getUser());
                $entityManager->persist($event);

                $brochureFiles = $form->get('fileName')->getData();
                $isFirstImage = true;

                foreach ($brochureFiles as $brochureFile) {
                    if ($brochureFile) {
                        $gallery = new Gallery();
                        $filename = $fileUploader->upload($brochureFile, $event);
                        $gallery->setEvent($event);
                        $gallery->setname($filename);
                        $gallery->setIsMain($isFirstImage);
                        $isFirstImage = false;
                        $event->addGallery($gallery);
                        $entityManager->persist($gallery);
                    }
                }

                $entityManager->flush();
                $this->addFlash('succes', 'Votre évènement à été créé avec succès. Vous pouvez le rétrouver dans la page gestion des évènements');
                return $this->redirectToRoute('app_user_profil', ['id' => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
            }
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

    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($event->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres événements.');
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setCreator($this->getUser());
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_CONTRIBUTEUR')]
    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($event->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres événements.');
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
