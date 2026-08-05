<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\TicketType;
use App\Enum\TicketLabel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TicketTypeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $data = [
        'event_0' => [
            ['label' => TicketLabel::STANDART, 'price' => 55, 'description' => 'Accès général en fosse'],
            ['label' => TicketLabel::VIP, 'price' => 140, 'description' => 'Accès VIP avec meet & greet'],
            ['label' => TicketLabel::PROMO, 'price' => 40, 'description' => 'Tarif réduit pour les étudiants'],
        ],
        'event_1' => [
            ['label' => TicketLabel::STANDART, 'price' => 60, 'description' => 'Accès général debout'],
            ['label' => TicketLabel::VIP, 'price' => 130, 'description' => 'Accès balcon assis'],
        ],
        'event_2' => [
            ['label' => TicketLabel::STANDART, 'price' => 75, 'description' => 'Accès général en fosse'],
            ['label' => TicketLabel::VIP, 'price' => 180, 'description' => 'Carré or, premier rang'],
            ['label' => TicketLabel::TABLE, 'price' => 220, 'description' => 'Table VIP pour 4 personnes'],
        ],
        'event_3' => [
            ['label' => TicketLabel::STANDART, 'price' => 45, 'description' => 'Accès général debout'],
            ['label' => TicketLabel::VIP, 'price' => 95, 'description' => 'Accès VIP avec zone dédiée'],
            ['label' => TicketLabel::PROMO, 'price' => 30, 'description' => 'Tarif réduit -18 ans'],
        ],
        'event_4' => [
            ['label' => TicketLabel::STANDART, 'price' => 25, 'description' => 'Accès général'],
            ['label' => TicketLabel::TABLE, 'price' => 120, 'description' => 'Table haute pour 2 personnes'],
        ],
        'event_5' => [
            ['label' => TicketLabel::STANDART, 'price' => 65, 'description' => 'Pass 1 jour'],
            ['label' => TicketLabel::VIP, 'price' => 150, 'description' => 'Pass 2 jours + zone VIP'],
            ['label' => TicketLabel::ENFANT, 'price' => 20, 'description' => 'Tarif enfant (- de 12 ans)'],
        ],
        'event_6' => [
            ['label' => TicketLabel::STANDART, 'price' => 35, 'description' => 'Accès général'],
            ['label' => TicketLabel::PROMO, 'price' => 22, 'description' => 'Tarif réduit étudiant'],
        ],
        'event_7' => [
            ['label' => TicketLabel::STANDART, 'price' => 30, 'description' => 'Entrée simple'],
            ['label' => TicketLabel::VIP, 'price' => 70, 'description' => 'Accès backstage + vestiaire'],
        ],
        'event_8' => [
            ['label' => TicketLabel::STANDART, 'price' => 0, 'description' => 'Entrée gratuite, réservation obligatoire'],
        ],
        'event_9' => [
            ['label' => TicketLabel::STANDART, 'price' => 15, 'description' => 'Accès à la journée'],
            ['label' => TicketLabel::ENFANT, 'price' => 5, 'description' => 'Tarif enfant (- de 12 ans)'],
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
