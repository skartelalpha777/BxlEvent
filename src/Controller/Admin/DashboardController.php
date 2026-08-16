<?php

namespace App\Controller\Admin;

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
        private readonly ChartBuilderInterface $chartBuilder
    ) {}

    public function index(): Response
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
                    'label' => 'Statistique de utilisateurs inscrit par mois',
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
        return $this->render('admin/dashboard.admin.html.twig', [
            'chart' => $chart,
        ]);
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
    }
}
