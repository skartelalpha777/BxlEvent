<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LocationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du lieu'),
            TextField::new('street', 'Rue'),
            IntegerField::new('number', 'Numéro'),
            IntegerField::new('postcode', 'Code postal'),
            TextField::new('city', 'Ville'),
            TextField::new('details', 'Détails')->hideOnIndex(),
            AssociationField::new('events', 'Événements')->hideOnForm(),
        ];
    }
}