<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email', 'Email'),
            TextField::new('firstName', 'Prénom'),
            TextField::new('lastName', 'Nom'),
            TextField::new('profileImg', 'Photo de profil')->hideOnIndex(),
            TextField::new('street', 'Rue')->hideOnIndex(),
            IntegerField::new('number', 'Numéro')->hideOnIndex(),
            IntegerField::new('postcode', 'Code postal')->hideOnIndex(),
            TextField::new('city', 'Ville')->hideOnIndex(),
            DateField::new('dateRgpd', 'Consentement RGPD')->hideOnIndex(),
            ChoiceField::new('role', 'Rôle')->setChoices([
                'Administrateur' => UserRole::ADMIN,
                'Contributeur' => UserRole::CONTRIBUTEUR,
                'Membre' => UserRole::MEMBRE,
            ]),
            AssociationField::new('events', 'Événements créés')->hideOnForm()->onlyOnDetail(),
            AssociationField::new('tickets', 'Billets')->hideOnForm()->onlyOnDetail(),
            AssociationField::new('orders', 'Commandes')->hideOnForm()->onlyOnDetail(),
            AssociationField::new('reports', 'Signalements')->hideOnForm()->onlyOnDetail(),
        ];
    }
}