<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Enum\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $events = [
        'event_0' => [
            'title' => 'Scorpions - Rock Legends Tour',
            'shortDescription' => 'Le groupe légendaire de retour à Bruxelles pour une tournée exceptionnelle.',
            'description' => 'Scorpions revient sur scène avec ses plus grands tubes pour une soirée rock inoubliable au cœur de Bruxelles.',
            'date' => '2026-08-15',
            'hour' => '20:00',
            'status' => Status::VALIDATED,
            'location' => 'location_ing_arena',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_1',
            'isFeatured' => true,
        ],
        'event_1' => [
            'title' => 'Massive Attack Live',
            'shortDescription' => 'Le groupe pionnier du trip-hop électrise Forest National.',
            'description' => "Massive Attack présente son univers sonore unique lors d'un concert immersif à Forest National.",
            'date' => '2026-08-29',
            'hour' => '20:30',
            'status' => Status::VALIDATED,
            'location' => 'location_forest_national',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_2',
        ],
        'event_2' => [
            'title' => 'U2 - With Or Without You Tour',
            'shortDescription' => 'U2 fait escale à Bruxelles pour un show gigantesque.',
            'description' => "Le groupe irlandais mythique enflamme l'ING Arena avec ses classiques intemporels et une scénographie spectaculaire.",
            'date' => '2026-09-12',
            'hour' => '19:30',
            'status' => Status::VALIDATED,
            'location' => 'location_ing_arena',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_1',
            'isFeatured' => true,
        ],
        'event_3' => [
            'title' => 'Damso - QALF Infinity Tour',
            'shortDescription' => 'Damso enflamme Forest National pour une date unique.',
            'description' => "Le rappeur bruxellois Damso revient sur les terres qui l'ont vu grandir pour un show intense et personnel.",
            'date' => '2026-09-26',
            'hour' => '20:00',
            'status' => Status::VALIDATED,
            'location' => 'location_forest_national',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_2',
        ],
        'event_4' => [
            'title' => 'Showcase L2B',
            'shortDescription' => "Un showcase intimiste dans la salle mythique de l'Ancienne Belgique.",
            'description' => 'Un événement en petit comité pour découvrir les artistes de la scène bruxelloise dans une ambiance chaleureuse.',
            'date' => '2026-10-03',
            'hour' => '19:00',
            'status' => Status::VALIDATED,
            'location' => 'location_ancienne_belgique',
            'categories' => ['category_showcase'],
            'organizer' => 'organizer_1',
        ],
        'event_5' => [
            'title' => 'Classic 21 Festival',
            'shortDescription' => "Le grand festival rock en plein air, au pied de l'Atomium.",
            'description' => 'Deux jours de concerts en plein air avec des artistes belges et internationaux, organisés par Classic 21.',
            'date' => '2026-10-17',
            'hour' => '14:00',
            'status' => Status::VALIDATED,
            'location' => 'location_parc_osseghem',
            'categories' => ['category_festival'],
            'organizer' => 'organizer_2',
            'isFeatured' => true,
        ],
        'event_6' => [
            'title' => 'Leto & Guy2bezbar',
            'shortDescription' => 'Deux artistes montants de la scène rap belge, sur une même scène.',
            'description' => "Leto et Guy2bezbar réunissent leur public pour une soirée énergique à l'Ancienne Belgique.",
            'date' => '2026-10-31',
            'hour' => '20:15',
            'status' => Status::VALIDATED,
            'location' => 'location_ancienne_belgique',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_1',
        ],
        'event_7' => [
            'title' => 'Nuit Électro Bruxelles',
            'shortDescription' => 'Une nuit électro non-stop au Cirque Royal.',
            'description' => 'Les meilleurs DJs de la scène électro belge et internationale se relaient toute la nuit au Cirque Royal.',
            'date' => '2026-11-14',
            'hour' => '23:00',
            'status' => Status::NOTCHECKED,
            'location' => 'location_cirque_royal',
            'categories' => ['category_festival'],
            'organizer' => 'organizer_2',
            'isFeatured' => true,
        ],
        'event_8' => [
            'title' => 'Jazz & Blues Night',
            'shortDescription' => 'Une soirée jazz et blues gratuite à Bozar.',
            'description' => 'Une programmation jazz et blues accessible à tous, dans le cadre prestigieux de Bozar.',
            'date' => '2026-11-28',
            'hour' => '19:30',
            'status' => Status::VALIDATED,
            'location' => 'location_bozar',
            'categories' => ['category_gratuit', 'category_concert'],
            'organizer' => 'organizer_1',
        ],
        'event_9' => [
            'title' => "Bruxelles Open Air Festival",
            'shortDescription' => "Le grand rendez-vous festif de fin d'année en plein air.",
            'description' => "Musique, food trucks et animations pour clôturer l'année en beauté au Parc d'Osseghem.",
            'date' => '2026-12-12',
            'hour' => '15:00',
            'status' => Status::NOTCHECKED,
            'location' => 'location_parc_osseghem',
            'categories' => ['category_festival', 'category_gratuit'],
            'organizer' => 'organizer_2',
        ],
        ];

        foreach ($events as $reference => $data) {
            $event = new Event();
            $event->setTitle($data['title']);
            $event->setShortDescription($data['shortDescription']);
            $event->setDescription($data['description']);
            $event->setDate(new \DateTime($data['date']));
            $event->setHour(new \DateTime($data['hour']));
            $event->setStatus($data['status']);
            $event->setLocation($this->getReference($data['location'], Location::class));
            $event->setCreator($this->getReference($data['organizer'], User::class));
            $event->setIsFeatured($data['isFeatured'] ?? false);

            foreach ($data['categories'] as $categoryReference) {
                $event->addCategory($this->getReference($categoryReference, Categorie::class));
            }

            $manager->persist($event);
            $this->addReference($reference, $event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategorieFixtures::class,
            LocationFixtures::class,
            UserFixtures::class,
        ];
    }
}
