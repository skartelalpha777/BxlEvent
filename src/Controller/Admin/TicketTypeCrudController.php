<?php

namespace App\Controller\Admin;

use App\Entity\TicketType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('label', 'Type'),
            IntegerField::new('price', 'Prix (€)'),
            IntegerField::new('maxTicket', 'Limite de billets')->setHelp('Laisser vide pour une quantité illimitée.'),
            TextareaField::new('description', 'Description'),
            AssociationField::new('event', 'Événement'),
            AssociationField::new('tickets', 'Billets vendus')->hideOnForm()->onlyOnDetail(),
        ];
    }
}