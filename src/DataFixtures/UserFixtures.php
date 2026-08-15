<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    // le mot de passe pour ce connecter est: password
    public function load(ObjectManager $manager): void
    {
        $Users = [
            'organizer_1' => ['email' => 'organisateur1@gmail.com', 'firstName' => 'Sophie', 'lastName' => 'Lambert'],
            'organizer_2' => ['email' => 'organisateur2@gmail.com', 'firstName' => 'Thomas', 'lastName' => 'Dubois'],
            'user1_' => ['email' => 'user1@gmail.com', 'firstName' => 'Françcois', 'lastName' => 'Leblanc'],
            'user_2' => ['email' => 'user2@gmail.com', 'firstName' => 'Emanuel', 'lastName' => 'Junior'],
            'Admin' => ['email' => 'admin@gmail.com', 'firstName' => 'Alpha', 'lastName' => 'Diallo'],
        ];

        foreach ($Users as $reference => $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            if ($data['firstName'] == 'Alpha') {
                $user->setRole(UserRole::ADMIN);
            } else if ($data['firstName'] == 'Sophie' || $data['firstName'] == 'Thomas') {
                $user->setRole(UserRole::CONTRIBUTEUR);
            } else {
                $user->setRole(UserRole::MEMBRE);
            }

            $user->setDateRgpd(new \DateTime());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $this->addReference($reference, $user);
        }

        $manager->flush();
    }
}
