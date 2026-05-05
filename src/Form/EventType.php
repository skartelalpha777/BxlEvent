<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\Status;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('date')
            ->add('hour')
            ->add('shortDescription', TextType::class, [
    'constraints' => [
        new NotBlank(message: 'La description courte est obligatoire.'),
        new Length( 
            min: 50,
            max: 110,
            minMessage: 'Votre description doit faire au moins {{ limit }} caractères.',
            maxMessage: 'Votre description ne peut pas dépasser {{ limit }} caractères.',
        )
    ]
])
            /*
            ->add('status', EnumType::class, [
                'class' => Status::class,
                'choice_label' => 'value',
            ])*/

            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
            ])
            ->add('creator', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('categories', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
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
