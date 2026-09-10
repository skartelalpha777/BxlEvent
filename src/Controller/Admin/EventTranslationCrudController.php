<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventTranslation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventTranslationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventTranslation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Traduction')
            ->setEntityLabelInPlural('Traductions d\'évènements')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('event', 'Évènement')
                ->setFormType(EntityType::class)
                ->setFormTypeOptions([
                    'class' => Event::class,
                    'choice_label' => 'title',
                    'placeholder' => '-- Choisir un évènement --',
                ]),
            ChoiceField::new('locale', 'Langue')->setChoices([
                'Anglais' => 'en',
                'Néerlandais' => 'nl',
                'Espagnol' => 'es',
            ]),
            TextareaField::new('description', 'Description traduite'),
        ];
    }
}
