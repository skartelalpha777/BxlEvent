<?php

namespace App\Controller\Admin;

use App\Entity\Reports;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ReportsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reports::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('date', 'Date')->hideOnForm(),
            TextareaField::new('description', 'Description'),
            BooleanField::new('treated', 'Traité'),
            AssociationField::new('event', 'Événement signalé'),
            AssociationField::new('user', 'Signalé par'),
            AssociationField::new('category', 'Catégorie'),
        ];
    }
}