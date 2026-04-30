<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\RegistrationFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $allowedRoles = [UserRole::MEMBRE, UserRole::CONTRIBUTEUR];
            $role = $form->get('role')->getData();
            // rajoute une sécurité supplémentaire pour ne pas q'un utilisateur puisse s'attribuer le role admin
            if (!in_array($role, $allowedRoles)) {
                throw new \Exception("Tentative d'injection de rôle non autorisé.");
            }
            //dd($form);

            $plainPassword = $form->get('plainPassword')->getData();
            $currentDate = new DateTime();


            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setDateRgpd($currentDate);
            $user->setRole($role);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home_event_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
