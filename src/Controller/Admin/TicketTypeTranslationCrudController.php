<?php

namespace App\Controller\Admin;

use App\Entity\TicketType;
use App\Entity\TicketTypeTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class TicketTypeTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketTypeTranslation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Traduction')
            ->setEntityLabelInPlural('Traductions de types de billets')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('ticketType', 'Type de billet')
                ->setFormType(EntityType::class)
                ->setFormTypeOptions([
                    'class' => TicketType::class,
                    'choice_label' =>  function (TicketType $ticketType) {
                        return  $ticketType->getLabel() . '--' .
                            $ticketType->getEvent()->getTitle();
                    },
                    'placeholder' => '-- Choisir un type de billet --',
                ]),
            ChoiceField::new('locale', 'Langue')->setChoices([
                'Anglais' => 'en',
                'Néerlandais' => 'nl',
                'Espagnol' => 'es',
            ]),
            TextField::new('label', 'Libellé traduit'),
            TextareaField::new('description', 'Description traduite'),
        ];
    }
}
