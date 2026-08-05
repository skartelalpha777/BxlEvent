<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Gallery;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GalleryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Réutilise les images déjà présentes dans public/assets/img/uploads/events/
        // plutôt que d'en télécharger de nouvelles.
        $data = [
            'event_0' => ['ConcertScorpions.jpg', 'ConcertScorpions2.jpg', 'ConcertScorpions3.jpg'],
            'event_1' => ['MassiveAttack.jpg', 'MassiveAttack2.jpg'],
            'event_2' => ['WithU2Day.jpg', 'WithU2Day2.jpg', 'WithU2Day3.jpg'],
            'event_3' => ['damso.jpg', 'damso2.jpg'],
            'event_4' => ['l2b.webp', 'l2b2.jpg', 'l2bmain.jpg'],
            'event_5' => ['Classic21Festival.jpg','Classic21FestivalMain.jpg'],
            'event_6' => ['Leto_Guy2bezbar.jpg','Leto_Guy2bezbarMain.jpg','Leto_Guy2bezbar2.jpg'],
            'event_7' => ['ElectroSymfony.png','ElectroSymfony2.jpg','ElectroSymfonyMain.jpg'],
            'event_8' => ['JazzBluesNightMain.jpg','JazzBluesNight2.jpg',''],
            'event_9' => ['BruxellesOpenAirFestivalMain.png','BruxellesOpenAirFestival.jpg'],
        ];

        foreach ($data as $eventReference => $images) {
            $event = $this->getReference($eventReference, Event::class);

            foreach ($images as $index => $imageName) {
                $gallery = new Gallery();
                $gallery->setName($imageName);
                $gallery->setIsMain($index === 0);
                $gallery->setEvent($event);
                $manager->persist($gallery);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EventFixtures::class];
    }
}
