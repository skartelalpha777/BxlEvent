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
    ) {}

    public function index(): Response
    {
        $userByMonth = $this->chartUsersByMonth();
        $revenuByMonth = $this->chartRevenuByMonth();
        $topCategories = $this->getTopCategories();
        return $this->render('admin/dashboard.admin.html.twig', [
            'userByMonth' => $userByMonth,
            'revenuByMonth' => $revenuByMonth,
            'topCategories' => $topCategories,
            'pendingEventsCount' => $this->eventRepository->countPendingValidation(),
            'untreatedReportsCount' => $this->reportsRepository->countUntreated(),
            'kpis' => $this->buildDatas(),
        ]);
    }

 
    private function buildDatas(): array
    {
        $now = new \DateTimeImmutable();
       
        $currentStart = $now->modify('first day of this month')->setTime(0, 0);
        $currentEnd = $now->modify('last day of this month')->setTime(23, 59, 59);
        $previousStart = $now->modify('first day of last month')->setTime(0, 0);
        $previousEnd = $now->modify('last day of last month')->setTime(23, 59, 59);
       
        $usersCurrent = $this->userRepository->countRegisteredBetween($currentStart, $currentEnd);
        $usersPrevious = $this->userRepository->countRegisteredBetween($previousStart, $previousEnd);

        $ordersCurrent = $this->orderRepository->countOrdersBetween($currentStart, $currentEnd);
        $ordersPrevious = $this->orderRepository->countOrdersBetween($previousStart, $previousEnd);

        $revenueCurrent = $this->orderRepository->sumRevenueBetween($currentStart, $currentEnd);
        $revenuePrevious = $this->orderRepository->sumRevenueBetween($previousStart, $previousEnd);

        $eventsCurrent = $this->eventRepository->countScheduledBetween($currentStart, $currentEnd);
        $eventsPrevious = $this->eventRepository->countScheduledBetween($previousStart, $previousEnd);

        return [
            [
                'label' => 'Utilisateurs',
                'icon' => 'fa-users',
                'color' => '#3b82f6',
                'value' => $usersCurrent,
                'trend' => $this->percentVariation($usersCurrent, $usersPrevious),
            ],
            [
                'label' => 'Commandes',
                'icon' => 'fa-cart-shopping',
                'color' => '#8b5cf6',
                'value' => $ordersCurrent,
                'trend' => $this->percentVariation($ordersCurrent, $ordersPrevious),
            ],
            [
                'label' => 'Chiffre d\'affaire',
                'icon' => 'fa-euro-sign',
                'color' => '#10b981',
                'value' => number_format($revenueCurrent) . ' €',
                'trend' => $this->percentVariation($revenueCurrent, $revenuePrevious),
            ],
            [
                'label' => 'Évènements',
                'icon' => 'fa-calendar-alt',
                'color' => '#f97316',
                'value' => $eventsCurrent,
                'trend' => $this->percentVariation($eventsCurrent, $eventsPrevious),
            ],
        ];
    }

    /**
     * Allow to calculate the variation between two periods usig Variation en % = (Valeur d'arrivée -Valeur de départ)/(Valeur de départ) * 100
     * @param float $current the end value
     * @param float $previous the start value
     * @return float the variation
     */
    private function percentVariation(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getTopCategories()
    {
        $categories = $this->categorieRepository->getTopCategories();
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
    private function chartUsersByMonth()
    {

        $mois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $users = $this->userRepository->getUsersByMonth();
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
    private function chartRevenuByMonth()
    {

        $revenu = $this->orderRepository->getOrderByMonth();
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

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addAssetMapperEntry('app');
    }

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

        yield MenuItem::section('Statistique');
        yield MenuItem::linkToDashboard('Utilisateurs inscrits par mois', 'fas fa-chart-line');
        yield MenuItem::linkToDashboard('Chiffre d\'affaire par mois', 'fas fa-chart-line');
    }
}
