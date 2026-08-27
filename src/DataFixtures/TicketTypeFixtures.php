<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\TicketType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TicketTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $data = [
        'event_0' => [
            ['label' => 'Standart', 'price' => 55, 'description' => 'Accès général en fosse'],
            ['label' => 'Vip', 'price' => 140, 'description' => 'Accès VIP avec meet & greet'],
            ['label' => 'Promo', 'price' => 40, 'description' => 'Tarif réduit pour les étudiants'],
        ],
        'event_1' => [
            ['label' => 'Standart', 'price' => 60, 'description' => 'Accès général debout'],
            ['label' => 'Vip', 'price' => 130, 'description' => 'Accès balcon assis'],
        ],
        'event_2' => [
            ['label' => 'Standart', 'price' => 75, 'description' => 'Accès général en fosse'],
            ['label' => 'Vip', 'price' => 180, 'description' => 'Carré or, premier rang'],
            ['label' => 'Table', 'price' => 220, 'description' => 'Table VIP pour 4 personnes'],
        ],
        'event_3' => [
            ['label' => 'Standart', 'price' => 45, 'description' => 'Accès général debout'],
            ['label' => 'Vip', 'price' => 95, 'description' => 'Accès VIP avec zone dédiée'],
            ['label' => 'Promo', 'price' => 30, 'description' => 'Tarif réduit -18 ans'],
        ],
        'event_4' => [
            ['label' => 'Standart', 'price' => 25, 'description' => 'Accès général'],
            ['label' => 'Table', 'price' => 120, 'description' => 'Table haute pour 2 personnes'],
        ],
        'event_5' => [
            ['label' => 'Standart', 'price' => 65, 'description' => 'Pass 1 jour'],
            ['label' => 'Vip', 'price' => 150, 'description' => 'Pass 2 jours + zone VIP'],
            ['label' => 'Enfant', 'price' => 20, 'description' => 'Tarif enfant (- de 12 ans)'],
        ],
        'event_6' => [
            ['label' => 'Standart', 'price' => 35, 'description' => 'Accès général'],
            ['label' => 'Promo', 'price' => 22, 'description' => 'Tarif réduit étudiant'],
        ],
        'event_7' => [
            ['label' => 'Standart', 'price' => 30, 'description' => 'Entrée simple'],
            ['label' => 'Vip', 'price' => 70, 'description' => 'Accès backstage + vestiaire'],
        ],
        'event_8' => [
            ['label' => 'Standart', 'price' => 0, 'description' => 'Entrée gratuite, réservation obligatoire'],
        ],
        'event_9' => [
            ['label' => 'Standart', 'price' => 15, 'description' => 'Accès à la journée'],
            ['label' => 'Enfant', 'price' => 5, 'description' => 'Tarif enfant (- de 12 ans)'],
        ],
        ];

        foreach ($data as $eventReference => $ticketTypes) {
            $event = $this->getReference($eventReference, Event::class);

            foreach ($ticketTypes as $ticketTypeData) {
                $ticketType = new TicketType();
                $ticketType->setLabel($ticketTypeData['label']);
                $ticketType->setPrice($ticketTypeData['price']);
                $ticketType->setDescription($ticketTypeData['description']);
                $ticketType->setEvent($event);
                $manager->persist($ticketType);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EventFixtures::class];
    }
}
