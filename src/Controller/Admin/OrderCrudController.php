<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('reference', 'Référence'),
            MoneyField::new('totalPrice', 'Montant total')->setCurrency('EUR')->setStoredAsCents(false),
            ChoiceField::new('status', 'Statut')->setChoices([
                'Payée' => OrderStatus::Paid,
                'En attente' => OrderStatus::Pending,
                'Annulée' => OrderStatus::Cancelled,
            ]),
            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
            AssociationField::new('user', 'Client'),
            AssociationField::new('tickets', 'Billets')->hideOnForm()->onlyOnDetail(),
        ];
    }
}