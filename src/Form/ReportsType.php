<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Reports;
use App\Entity\ReportCategory;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;

class ReportsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('date')
            ->add('description')
            ->add('category', EntityType::class, [
                'class' => ReportCategory::class,
                'choice_label' => 'label',
                'expanded' => true
            ])
            /* ->add('treated')
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'id',
            ])
            
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ]) */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reports::class,
        ]);
    }
}
