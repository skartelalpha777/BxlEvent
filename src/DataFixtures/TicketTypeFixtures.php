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
        $labelTranslations = [
            'Standart' => ['en' => 'Standard', 'nl' => 'Standaard', 'es' => 'Estándar'],
            'Vip' => ['en' => 'Vip', 'nl' => 'Vip', 'es' => 'Vip'],
            'Promo' => ['en' => 'Discount', 'nl' => 'Korting', 'es' => 'Descuento'],
            'Table' => ['en' => 'Table', 'nl' => 'Tafel', 'es' => 'Mesa'],
            'Enfant' => ['en' => 'Child', 'nl' => 'Kind', 'es' => 'Niño'],
        ];

        $data = [
        'event_0' => [
            ['label' => 'Standart', 'price' => 55, 'description' => 'Accès général en fosse', 'descriptionTranslations' => ['en' => 'General standing access in the pit', 'nl' => 'Algemene staanplaats in de pit', 'es' => 'Acceso general de pie en el foso']],
            ['label' => 'Vip', 'price' => 140, 'description' => 'Accès VIP avec meet & greet', 'descriptionTranslations' => ['en' => 'VIP access with meet & greet', 'nl' => 'VIP-toegang met meet & greet', 'es' => 'Acceso VIP con encuentro con los artistas']],
            ['label' => 'Promo', 'price' => 40, 'description' => 'Tarif réduit pour les étudiants', 'descriptionTranslations' => ['en' => 'Discounted rate for students', 'nl' => 'Kortingstarief voor studenten', 'es' => 'Tarifa reducida para estudiantes']],
        ],
        'event_1' => [
            ['label' => 'Standart', 'price' => 60, 'description' => 'Accès général debout', 'descriptionTranslations' => ['en' => 'General standing access', 'nl' => 'Algemene staanplaats', 'es' => 'Acceso general de pie']],
            ['label' => 'Vip', 'price' => 130, 'description' => 'Accès balcon assis', 'descriptionTranslations' => ['en' => 'Seated balcony access', 'nl' => 'Zitplaats op het balkon', 'es' => 'Acceso a balcón con asiento']],
        ],
        'event_2' => [
            ['label' => 'Standart', 'price' => 75, 'description' => 'Accès général en fosse', 'descriptionTranslations' => ['en' => 'General standing access in the pit', 'nl' => 'Algemene staanplaats in de pit', 'es' => 'Acceso general de pie en el foso']],
            ['label' => 'Vip', 'price' => 180, 'description' => 'Carré or, premier rang', 'descriptionTranslations' => ['en' => 'Gold section, front row', 'nl' => 'Gouden zone, eerste rij', 'es' => 'Zona oro, primera fila']],
            ['label' => 'Table', 'price' => 220, 'description' => 'Table VIP pour 4 personnes', 'descriptionTranslations' => ['en' => 'VIP table for 4 people', 'nl' => 'VIP-tafel voor 4 personen', 'es' => 'Mesa VIP para 4 personas']],
        ],
        'event_3' => [
            ['label' => 'Standart', 'price' => 45, 'description' => 'Accès général debout', 'descriptionTranslations' => ['en' => 'General standing access', 'nl' => 'Algemene staanplaats', 'es' => 'Acceso general de pie']],
            ['label' => 'Vip', 'price' => 95, 'description' => 'Accès VIP avec zone dédiée', 'descriptionTranslations' => ['en' => 'VIP access with dedicated area', 'nl' => 'VIP-toegang met eigen zone', 'es' => 'Acceso VIP con zona reservada']],
            ['label' => 'Promo', 'price' => 30, 'description' => 'Tarif réduit -18 ans', 'descriptionTranslations' => ['en' => 'Discounted rate for under-18s', 'nl' => 'Kortingstarief -18 jaar', 'es' => 'Tarifa reducida para menores de 18 años']],
        ],
        'event_4' => [
            ['label' => 'Standart', 'price' => 25, 'description' => 'Accès général', 'descriptionTranslations' => ['en' => 'General access', 'nl' => 'Algemene toegang', 'es' => 'Acceso general']],
            ['label' => 'Table', 'price' => 120, 'description' => 'Table haute pour 2 personnes', 'descriptionTranslations' => ['en' => 'High table for 2 people', 'nl' => 'Statafel voor 2 personen', 'es' => 'Mesa alta para 2 personas']],
        ],
        'event_5' => [
            ['label' => 'Standart', 'price' => 65, 'description' => 'Pass 1 jour', 'descriptionTranslations' => ['en' => '1-day pass', 'nl' => '1-dagpas', 'es' => 'Pase de 1 día']],
            ['label' => 'Vip', 'price' => 150, 'description' => 'Pass 2 jours + zone VIP', 'descriptionTranslations' => ['en' => '2-day pass + VIP area', 'nl' => '2-dagenpas + VIP-zone', 'es' => 'Pase de 2 días + zona VIP']],
            ['label' => 'Enfant', 'price' => 20, 'description' => 'Tarif enfant (- de 12 ans)', 'descriptionTranslations' => ['en' => 'Child rate (under 12)', 'nl' => 'Kindertarief (- 12 jaar)', 'es' => 'Tarifa infantil (menores de 12 años)']],
        ],
        'event_6' => [
            ['label' => 'Standart', 'price' => 35, 'description' => 'Accès général', 'descriptionTranslations' => ['en' => 'General access', 'nl' => 'Algemene toegang', 'es' => 'Acceso general']],
            ['label' => 'Promo', 'price' => 22, 'description' => 'Tarif réduit étudiant', 'descriptionTranslations' => ['en' => 'Discounted student rate', 'nl' => 'Kortingstarief student', 'es' => 'Tarifa reducida para estudiantes']],
        ],
        'event_7' => [
            ['label' => 'Standart', 'price' => 30, 'description' => 'Entrée simple', 'descriptionTranslations' => ['en' => 'Single entry', 'nl' => 'Enkele toegang', 'es' => 'Entrada simple']],
            ['label' => 'Vip', 'price' => 70, 'description' => 'Accès backstage + vestiaire', 'descriptionTranslations' => ['en' => 'Backstage access + cloakroom', 'nl' => 'Backstage-toegang + garderobe', 'es' => 'Acceso backstage + guardarropa']],
        ],
        'event_8' => [
            ['label' => 'Standart', 'price' => 0, 'description' => 'Entrée gratuite, réservation obligatoire', 'descriptionTranslations' => ['en' => 'Free entry, booking required', 'nl' => 'Gratis toegang, reservering verplicht', 'es' => 'Entrada gratuita, reserva obligatoria']],
        ],
        'event_9' => [
            ['label' => 'Standart', 'price' => 15, 'description' => 'Accès à la journée', 'descriptionTranslations' => ['en' => 'Day access', 'nl' => 'Dagtoegang', 'es' => 'Acceso de un día']],
            ['label' => 'Enfant', 'price' => 5, 'description' => 'Tarif enfant (- de 12 ans)', 'descriptionTranslations' => ['en' => 'Child rate (under 12)', 'nl' => 'Kindertarief (- 12 jaar)', 'es' => 'Tarifa infantil (menores de 12 años)']],
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

                foreach ($ticketTypeData['descriptionTranslations'] ?? [] as $locale => $description) {
                    $translation = $ticketType->translate($locale, false);
                    $translation->setLabel($labelTranslations[$ticketTypeData['label']][$locale] ?? $ticketTypeData['label']);
                    $translation->setDescription($description);
                }
                $ticketType->mergeNewTranslations();

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
