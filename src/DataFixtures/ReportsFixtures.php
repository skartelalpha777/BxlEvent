<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\ReportCategory;
use App\Entity\Reports;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReportsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $reports = [
            [
                'date' => '2026-07-10',
                'description' => "L'affiche de l'événement contient une image qui n'a rien à voir avec le concert annoncé.",
                'treated' => false,
                'event' => 'event_0',
                'user' => 'user1_',
                'category' => 'report_category_inapproprie',
            ],
            [
                'date' => '2026-07-14',
                'description' => "L'heure indiquée sur la page ne correspond pas à celle communiquée par la salle.",
                'treated' => true,
                'event' => 'event_1',
                'user' => 'user_2',
                'category' => 'report_category_info_erronee',
            ],
            [
                'date' => '2026-07-20',
                'description' => "Je reçois des emails suspects demandant de payer les billets en dehors du site.",
                'treated' => false,
                'event' => 'event_2',
                'user' => 'user1_',
                'category' => 'report_category_arnaque',
            ],
            [
                'date' => '2026-07-25',
                'description' => "L'organisateur a annoncé le report de la date sur ses réseaux sociaux, la fiche n'est pas à jour.",
                'treated' => false,
                'event' => 'event_5',
                'user' => 'user_2',
                'category' => 'report_category_annule',
            ],
            [
                'date' => '2026-08-01',
                'description' => "La description de l'événement comporte plusieurs fautes qui la rendent peu claire.",
                'treated' => true,
                'event' => 'event_8',
                'user' => 'user1_',
                'category' => 'report_category_autre',
            ],
        ];

        foreach ($reports as $data) {
            $report = new Reports();
            $report->setDate(new \DateTime($data['date']));
            $report->setDescription($data['description']);
            $report->setTreated($data['treated']);
            $report->setEvent($this->getReference($data['event'], Event::class));
            $report->setUser($this->getReference($data['user'], User::class));
            $report->setCategory($this->getReference($data['category'], ReportCategory::class));

            $manager->persist($report);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EventFixtures::class,
            UserFixtures::class,
            ReportCategoryFixtures::class,
        ];
    }
}
