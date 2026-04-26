<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRole;
use Egulias\EmailValidator;
use Egulias\EmailValidator\Warning\EmailTooLong;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\Cache\ChoiceAttr;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'Utilisateur' => UserRole::MEMBRE,
                    'Contributeur' => UserRole::CONTRIBUTEUR,
                ],
                'choice_attr' => [
                    'Utilisateur' => ['data-titre' => 'Utilisateur (BxlEventeur)', 'data-description' => 'Je cherche des sorties'],
                    'Contributeur' => ['data-titre' => 'Contributeur (Organisateur)', 'data-description' => 'je veux publier mes propres évèenements, gérer ma programation '],

                ],
                'required' => true,
                'invalid_message' => 'Veuillez sélectionner un type d\'utilisateur valide.',
                //Pour affichage en boutons radio (et non liste déroulante ou checkbox)
                'expanded' => true,
                //limite le choix à un seul choix possible
                'multiple' => false,

                'constraints' => [
                    new NotBlank(message: 'Vous devez sélectionner une option d\'utilisation.')

                ]
            ])

            ->add('firstName')
            ->add('lastName')

            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password',
                    ),/*
                    new Length(
                        min: 6,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        max: 4096,
                    ),*/

                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "<p> Je suis d'accord avec les <a href='#'> conditions utilisation  </a> </p>",
                'label_html' => true,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter nos conditions.',
                    ),

                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
