<?php

namespace App\Controller\Admin;

use App\Entity\TicketType;
use App\Enum\TicketLabel;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

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
            ChoiceField::new('label', 'Type')->setChoices([
                'Standard' => TicketLabel::STANDART,
                'Enfant' => TicketLabel::ENFANT,
                'VIP' => TicketLabel::VIP,
                'Promo' => TicketLabel::PROMO,
                'Table' => TicketLabel::TABLE,
            ]),
            IntegerField::new('price', 'Prix (€)'),
            TextareaField::new('description', 'Description'),
            AssociationField::new('event', 'Événement'),
            AssociationField::new('tickets', 'Billets vendus')->hideOnForm()->onlyOnDetail(),
        ];
    }
}