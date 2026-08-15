<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {

        return $this->render('admin/dashboard.admin.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration');
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
    }
}
