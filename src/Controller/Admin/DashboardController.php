<?php

namespace App\Controller\Admin;

use App\Repository\CategorieRepository;
use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Repository\ReportsRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly OrderRepository $orderRepository,
        private readonly CategorieRepository $categorieRepository,
        private readonly EventRepository $eventRepository,
        private readonly ReportsRepository $reportsRepository,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * Page d'accueil du back-office : KPIs, graphiques (utilisateurs/mois, revenus/mois, top catégories)
     * et compteurs de modération, tous filtrables sur une période via getFilteredPeriod().
     */
    public function index(): Response
    {
        [$start, $end] = $this->getFilteredPeriod();
        $userByMonth = $this->chartUsersByMonth($start, $end);
        $revenuByMonth = $this->chartRevenuByMonth($start, $end);
        $topCategories = $this->getTopCategories($start, $end);
        return $this->render('admin/dashboard.admin.html.twig', [
            'userByMonth' => $userByMonth,
            'revenuByMonth' => $revenuByMonth,
            'topCategories' => $topCategories,
            'pendingEventsCount' => $this->eventRepository->countPendingValidation(),
            'untreatedReportsCount' => $this->reportsRepository->countUntreated(),
            'kpis' => $this->buildDatas($start, $end),
        ]);
    }

    /**
     * Lit et valide le filtre de dates soumis par le formulaire (start-date/end-date + token CSRF).
     * @return array{0: ?\DateTime, 1: ?\DateTime} [start, end], ou [null, null] si aucun filtre valide n'est fourni
     */
    private function getFilteredPeriod(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $submittedToken = $request->query->get('filter_token');
        if (!$this->isCsrfTokenValid('filter', $submittedToken)) {
            return [null, null];
        }

        $start = $request->query->get('start-date');
        $end = $request->query->get('end-date');
        if (!$start || !$end) {
            return [null, null];
        }

        return [new \DateTime($start), new \DateTime($end)];
    }

    /**
     * Calcule les 4 KPIs (utilisateurs, commandes, chiffre d'affaire, évènements) sur la période filtrée,
     * ou sur le mois civil en cours si aucun filtre n'est fourni.
     * @param ?\DateTimeInterface $start début de la période filtrée (null si pas de filtre)
     * @param ?\DateTimeInterface $end fin de la période filtrée (null si pas de filtre)
     * @return array les 4 cartes KPI (label, icon, color, value)
     */
    private function buildDatas(?\DateTimeInterface $start, ?\DateTimeInterface $end): array
    {
        if ($start === null || $end === null) {
            $now = new \DateTimeImmutable();
            $start = $now->modify('first day of this month')->setTime(0, 0);
            $end = $now->modify('last day of this month')->setTime(23, 59, 59);
        }

        $usersCount = $this->userRepository->countRegisteredBetween($start, $end);
        $ordersCount = $this->orderRepository->countOrdersBetween($start, $end);
        $revenueCount = $this->orderRepository->sumRevenueBetween($start, $end);
        $eventsCount = $this->eventRepository->countScheduledBetween($start, $end);

        return [
            [
                'label' => 'Utilisateurs',
                'icon' => 'fa-users',
                'color' => '#3b82f6',
                'value' => $usersCount,
            ],
            [
                'label' => 'Commandes',
                'icon' => 'fa-cart-shopping',
                'color' => '#8b5cf6',
                'value' => $ordersCount,
            ],
            [
                'label' => 'Chiffre d\'affaire',
                'icon' => 'fa-euro-sign',
                'color' => '#10b981',
                'value' => number_format($revenueCount) . ' €',
            ],
            [
                'label' => 'Évènements',
                'icon' => 'fa-calendar-alt',
                'color' => '#f97316',
                'value' => $eventsCount,
            ],
        ];
    }

    /**
     * Construit le graphique en camembert du nombre d'évènements par catégorie, filtré sur la période si fournie.
     * @param ?\DateTimeInterface $start début de la période filtrée (null si pas de filtre)
     * @param ?\DateTimeInterface $end fin de la période filtrée (null si pas de filtre)
     * @return Chart
     */
    private function getTopCategories(?\DateTimeInterface $start, ?\DateTimeInterface $end)
    {
        $categories = $this->categorieRepository->getTopCategories($start, $end);
        $labels = [];
        $data = [];
        $backgorungColors = [];
        foreach ($categories as $category) {
            $cat = $this->categorieRepository->findBy(['id' => $category['categorie']]);
            $labels[] =  $cat[0]->getName();
            $data[] = $category['total'];
            $backgorungColors[] = sprintf('#%06X', random_int(0, 0xFFFFFF));
        }
        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Evènement par categorie',
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
                    'suggestedMax' => 20,
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
    /**
     * Construit le graphique en ligne du nombre d'utilisateurs inscrits par mois, filtré sur la période si fournie.
     * @param ?\DateTimeInterface $start début de la période filtrée (null si pas de filtre)
     * @param ?\DateTimeInterface $end fin de la période filtrée (null si pas de filtre)
     * @return Chart
     */
    private function chartUsersByMonth(?\DateTimeInterface $start, ?\DateTimeInterface $end)
    {

        $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $users = $this->userRepository->getUsersByMonth($start, $end);
        $labels = [];
        $data = [];
        foreach ($users as $user) {
            $labels[] = $mois[$user['month'] - 1];
            $data[] = $user['total'];
        }
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Utilisateurs inscrit par mois',
                    'backgroundColor' => '#1387c1',
                    'borderColor' => '#1387c1',
                    'data' => $data
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 20,
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
    /**
     * Construit le graphique en barres du chiffre d'affaire par mois, filtré sur la période si fournie.
     * @param ?\DateTimeInterface $start début de la période filtrée (null si pas de filtre)
     * @param ?\DateTimeInterface $end fin de la période filtrée (null si pas de filtre)
     * @return Chart
     */
    private function chartRevenuByMonth(?\DateTimeInterface $start, ?\DateTimeInterface $end)
    {

        $revenu = $this->orderRepository->getOrderByMonth($start, $end);
        $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $labels = [];
        $data = [];
        foreach ($revenu as $r) {
            $labels[] = $mois[$r['month'] - 1];
            $data[] = $r['total'];
        }
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Chiffre d\'affaire par mois en euro',
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(14, 21, 37, 0.2)',
                        'rgba(52, 55, 61, 0.2)',
                        'rgba(198, 165, 245, 0.2)',
                        'rgba(182, 197, 226, 0.2)',
                        'rgba(40, 183, 183, 0.2)',
                        'rgba(212, 230, 238, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(115, 204, 177)',
                        'rgb(25, 90, 37)',
                        'rgb(20, 16, 30)',
                        'rgb(153, 222, 84)',
                        'rgb(61, 63, 68)',
                        'rgb(172, 211, 33)',
                    ],
                    'borderWidth' => 1,
                    'barPercentage' => 0.1,
                    'data' => $data
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 20,
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

    /**
     * Configuration EasyAdmin du dashboard (appelée par le bundle, pas par notre code) : titre affiché dans le back-office.
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
    }

    /**
     * Configuration EasyAdmin des assets (appelée par le bundle) : charge l'entrée AssetMapper "app"
     * (assets/app.js) pour avoir les graphiques Chart.js et le zoom/pan sur les pages du back-office.
     */
    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addAssetMapperEntry('app');
    }

    /**
     * Configuration EasyAdmin du menu latéral (appelée par le bundle) : déclare les entrées de menu
     * et les regroupe par section (Catalogue, Billetterie, Utilisateurs & Modération).
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkTo(EventCrudController::class, 'Évènements', 'fas fa-calendar-alt')->setAction(Action::INDEX);
        yield MenuItem::linkTo(CategorieCrudController::class, 'Catégories', 'fas fa-tags')->setAction(Action::INDEX);
        yield MenuItem::linkTo(LocationCrudController::class, 'Lieux', 'fas fa-map-marker-alt')->setAction(Action::INDEX);
        yield MenuItem::linkTo(GalleryCrudController::class, 'Galerie', 'fas fa-images')->setAction(Action::INDEX);

        yield MenuItem::section('Billetterie');
        yield MenuItem::linkTo(TicketTypeCrudController::class, 'Types de billets', 'fas fa-ticket-alt')->setAction(Action::INDEX);
        yield MenuItem::linkTo(TicketCrudController::class, 'Billets', 'fas fa-qrcode')->setAction(Action::INDEX);
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fas fa-shopping-cart')->setAction(Action::INDEX);

        yield MenuItem::section('Utilisateurs & Modération');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fas fa-users')->setAction(Action::INDEX);
        yield MenuItem::linkTo(ReportsCrudController::class, 'Signalements', 'fas fa-flag')->setAction(Action::INDEX);
        yield MenuItem::linkTo(ReportCategoryCrudController::class, 'Catégories de signalement', 'fas fa-exclamation-circle')->setAction(Action::INDEX);
        yield MenuItem::linkTo(NewsletterCrudController::class, 'Newsletter', 'fas fa-envelope-open-text')->setAction(Action::INDEX);
 }
}
