<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\Status;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextField::new('shortDescription', 'Description courte'),
            TextEditorField::new('description', 'Description'),
            DateField::new('date', 'Date'),
            TimeField::new('hour', 'Heure'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'Validé' => Status::VALIDATED,
                'Refusé' => Status::REFUSED,
                'Non vérifié' => Status::NOTCHECKED,
            ]),
            BooleanField::new('isFeatured', 'Mis en avant'),
            AssociationField::new('location', 'Lieu'),
            AssociationField::new('creator', 'Organisateur'),
            AssociationField::new('categories', 'Catégories'),
            AssociationField::new('galleries', 'Galerie')->hideOnForm(),
            AssociationField::new('tickettypes', 'Types de billets')->hideOnForm(),
            AssociationField::new('tickets', 'Billets vendus')->hideOnForm()->onlyOnDetail(),
            AssociationField::new('reports', 'Signalements')->hideOnForm()->onlyOnDetail(),
        ];
    }
}