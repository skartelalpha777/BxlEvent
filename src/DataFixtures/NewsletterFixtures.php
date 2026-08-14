<?php

namespace App\DataFixtures;

use App\Entity\Newsletter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NewsletterFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $subscribers = [
            ['email' => 'claire.moreau@gmail.com', 'inscriptionDate' => '2026-06-02'],
            ['email' => 'nicolas.petit@gmail.com', 'inscriptionDate' => '2026-06-15'],
            ['email' => 'julie.simon@gmail.com', 'inscriptionDate' => '2026-06-28'],
            ['email' => 'kevin.laurent@gmail.com', 'inscriptionDate' => '2026-07-05'],
            ['email' => 'amelie.rousseau@gmail.com', 'inscriptionDate' => '2026-07-19'],
        ];

        foreach ($subscribers as $data) {
            $newsletter = new Newsletter();
            $newsletter->setEmail($data['email']);
            $newsletter->setInscriptionDate(new \DateTime($data['inscriptionDate']));

            $manager->persist($newsletter);
        }

        $manager->flush();
    }
}
