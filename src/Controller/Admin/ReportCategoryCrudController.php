<?php

namespace App\Controller\Admin;

use App\Entity\ReportCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReportCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReportCategory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('label', 'Libellé'),
            TextField::new('icon', 'Icône (classe Bootstrap Icons)'),
            AssociationField::new('reports', 'Signalements')->hideOnForm(),
        ];
    }
}