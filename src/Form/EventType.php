<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Event;
use App\Entity\Gallery;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class)
            ->add('date', DateType::class, [])
            ->add('hour', TimeType::class, [])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
                'placeholder' => '-- Créer un nouveau lieu --',
                'required' => false,
            ])
            // Champs utilisés uniquement si aucun lieu existant n'est sélectionné ci-dessus lors de la création de l'évènement afin de creer un nouveau lieu.
            ->add('newLocationName', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Nom du nouveau lieu'])
            ->add('newLocationStreet', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Rue'])
            ->add('newLocationNumber', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Numéro'])
            ->add('newLocationPostcode', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Code postal'])
            ->add('newLocationCity', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Ville'])
            ->add('categories', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])

            //permet d'ajouter des images a l'évènement
            ->add('fileName', FileType::class, [

                'label' => 'Afiches pour l\'évènement',
                'mapped' => false,
                'required' => true,
                'multiple' => true,
                'constraints' => [
                    new Assert\Count(
                        max: 3,
                        min: 1,
                        minMessage: 'Vous devez ajouter au moins une image',
                        maxMessage: 'Vous pouvez ajouter au maximum 3 images'
                    ),
                    new Assert\All([
                        new Assert\File(
                            extensions: ['jpg', 'jpeg', 'png', 'webp'],
                            extensionsMessage: 'Veillez ajouter au moins une image',
                        ),
                    ]),
                ],
            ])


            // Permet d'ajouter une catégorie qui n'existe pas encore dans la liste ci-dessus lors de la creation d'un évenement.
            ->add('newCategoryName', TextType::class, ['mapped' => false, 'required' => false, 'label' => 'Nouvelle catégorie'])
            ->add('tickettypes', CollectionType::class, [
                'entry_type' => TicketTypeType::class,
                'entry_options' => ['include_event' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
