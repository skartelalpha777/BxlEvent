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
            'description' => 'Scorpions revient sur scène avec ses plus grands tubes pour une soirée rock inoubliable au cœur de Bruxelles.',
            'translations' => [
                'en' => 'Scorpions returns to the stage with their greatest hits for an unforgettable rock night in the heart of Brussels.',
                'nl' => 'Scorpions keert terug op het podium met hun grootste hits voor een onvergetelijke rockavond in het hart van Brussel.',
                'es' => 'Scorpions vuelve a los escenarios con sus mayores éxitos para una noche de rock inolvidable en el corazón de Bruselas.',
            ],
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
            'description' => "Massive Attack présente son univers sonore unique lors d'un concert immersif à Forest National.",
            'translations' => [
                'en' => 'Massive Attack presents their unique sonic universe in an immersive concert at Forest National.',
                'nl' => 'Massive Attack presenteert hun unieke muzikale universum tijdens een meeslepend concert in Vorst Nationaal.',
                'es' => 'Massive Attack presenta su universo sonoro único en un concierto inmersivo en Forest National.',
            ],
            'date' => '2026-08-29',
            'hour' => '20:30',
            'status' => Status::VALIDATED,
            'location' => 'location_forest_national',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_2',
        ],
        'event_2' => [
            'title' => 'U2 - With Or Without You Tour',
            'description' => "Le groupe irlandais mythique enflamme l'ING Arena avec ses classiques intemporels et une scénographie spectaculaire.",
            'translations' => [
                'en' => 'The legendary Irish band sets the ING Arena ablaze with their timeless classics and a spectacular stage design.',
                'nl' => 'De legendarische Ierse band zet de ING Arena in vuur en vlam met hun tijdloze klassiekers en een spectaculaire enscenering.',
                'es' => 'La legendaria banda irlandesa incendia el ING Arena con sus clásicos atemporales y una escenografía espectacular.',
            ],
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
            'description' => "Le rappeur bruxellois Damso revient sur les terres qui l'ont vu grandir pour un show intense et personnel.",
            'translations' => [
                'en' => 'Brussels rapper Damso returns to the streets where he grew up for an intense and personal show.',
                'nl' => 'De Brusselse rapper Damso keert terug naar de plek waar hij opgroeide voor een intense en persoonlijke show.',
                'es' => 'El rapero bruselense Damso regresa a las calles donde creció para un espectáculo intenso y personal.',
            ],
            'date' => '2026-09-26',
            'hour' => '20:00',
            'status' => Status::VALIDATED,
            'location' => 'location_forest_national',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_2',
        ],
        'event_4' => [
            'title' => 'Showcase L2B',
            'description' => 'Un événement en petit comité pour découvrir les artistes de la scène bruxelloise dans une ambiance chaleureuse.',
            'translations' => [
                'en' => 'An intimate event to discover artists from the Brussels scene in a warm atmosphere.',
                'nl' => 'Een intiem evenement om artiesten uit de Brusselse scene te ontdekken in een warme sfeer.',
                'es' => 'Un evento íntimo para descubrir a los artistas de la escena bruselense en un ambiente cálido.',
            ],
            'date' => '2026-10-03',
            'hour' => '19:00',
            'status' => Status::VALIDATED,
            'location' => 'location_ancienne_belgique',
            'categories' => ['category_showcase'],
            'organizer' => 'organizer_1',
        ],
        'event_5' => [
            'title' => 'Classic 21 Festival',
            'description' => 'Deux jours de concerts en plein air avec des artistes belges et internationaux, organisés par Classic 21.',
            'translations' => [
                'en' => 'Two days of open-air concerts with Belgian and international artists, organised by Classic 21.',
                'nl' => 'Twee dagen openluchtconcerten met Belgische en internationale artiesten, georganiseerd door Classic 21.',
                'es' => 'Dos días de conciertos al aire libre con artistas belgas e internacionales, organizados por Classic 21.',
            ],
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
            'description' => "Leto et Guy2bezbar réunissent leur public pour une soirée énergique à l'Ancienne Belgique.",
            'translations' => [
                'en' => 'Leto and Guy2bezbar bring their fans together for an energetic night at the Ancienne Belgique.',
                'nl' => 'Leto en Guy2bezbar brengen hun fans samen voor een energieke avond in de Ancienne Belgique.',
                'es' => 'Leto y Guy2bezbar reúnen a su público para una noche llena de energía en el Ancienne Belgique.',
            ],
            'date' => '2026-10-31',
            'hour' => '20:15',
            'status' => Status::VALIDATED,
            'location' => 'location_ancienne_belgique',
            'categories' => ['category_concert'],
            'organizer' => 'organizer_1',
        ],
        'event_7' => [
            'title' => 'Nuit Électro Bruxelles',
            'description' => 'Les meilleurs DJs de la scène électro belge et internationale se relaient toute la nuit au Cirque Royal.',
            'translations' => [
                'en' => 'The best DJs from the Belgian and international electro scene take turns all night long at the Cirque Royal.',
                'nl' => "De beste dj's uit de Belgische en internationale elektroscene wisselen elkaar de hele nacht af in het Cirque Royal.",
                'es' => 'Los mejores DJs de la escena electrónica belga e internacional se turnan toda la noche en el Cirque Royal.',
            ],
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
            'description' => 'Une programmation jazz et blues accessible à tous, dans le cadre prestigieux de Bozar.',
            'translations' => [
                'en' => 'A jazz and blues programme accessible to everyone, in the prestigious setting of Bozar.',
                'nl' => 'Een jazz- en bluesprogramma toegankelijk voor iedereen, in het prestigieuze kader van Bozar.',
                'es' => 'Una programación de jazz y blues accesible para todos, en el prestigioso marco de Bozar.',
            ],
            'date' => '2026-11-28',
            'hour' => '19:30',
            'status' => Status::VALIDATED,
            'location' => 'location_bozar',
            'categories' => ['category_gratuit', 'category_concert'],
            'organizer' => 'organizer_1',
        ],
        'event_9' => [
            'title' => "Bruxelles Open Air Festival",
            'description' => "Musique, food trucks et animations pour clôturer l'année en beauté au Parc d'Osseghem.",
            'translations' => [
                'en' => "Music, food trucks and entertainment to close the year in style at Parc d'Osseghem.",
                'nl' => 'Muziek, food trucks en animatie om het jaar in stijl af te sluiten in het Ossegempark.',
                'es' => 'Música, food trucks y animación para cerrar el año a lo grande en el Parque de Osseghem.',
            ],
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

            foreach ($data['translations'] ?? [] as $locale => $description) {
                $event->translate($locale, false)->setDescription($description);
            }
            $event->mergeNewTranslations();

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
