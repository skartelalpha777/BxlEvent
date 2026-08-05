<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $locations = [
            'location_ing_arena' => ['name' => 'ING Arena', 'street' => 'Boulevard du Centenaire', 'number' => 20, 'postcode' => 1020, 'city' => 'Bruxelles', 'details' => 'Plus grande salle de concert de Belgique'],
            'location_forest_national' => ['name' => 'Forest National', 'street' => 'Avenue Victor Rousseau', 'number' => 208, 'postcode' => 1190, 'city' => 'Bruxelles', 'details' => 'Salle de concert historique'],
            'location_ancienne_belgique' => ['name' => 'Ancienne Belgique', 'street' => 'Boulevard Anspach', 'number' => 110, 'postcode' => 1000, 'city' => 'Bruxelles', 'details' => 'Salle de musiques actuelles'],
            'location_parc_osseghem' => ['name' => "Parc d'Osseghem", 'street' => "Avenue de l'Atomium", 'number' => 1, 'postcode' => 1020, 'city' => 'Bruxelles', 'details' => 'Parc en plein air au pied de l\'Atomium'],
            'location_cirque_royal' => ['name' => 'Cirque Royal', 'street' => "Rue de l'Enseignement", 'number' => 81, 'postcode' => 1000, 'city' => 'Bruxelles', 'details' => 'Salle de spectacle Art Déco'],
            'location_bozar' => ['name' => 'Bozar', 'street' => 'Rue Ravenstein', 'number' => 23, 'postcode' => 1000, 'city' => 'Bruxelles', 'details' => 'Centre des beaux-arts'],
        ];

        foreach ($locations as $reference => $data) {
            $location = new Location();
            $location->setName($data['name']);
            $location->setStreet($data['street']);
            $location->setNumber($data['number']);
            $location->setPostcode($data['postcode']);
            $location->setCity($data['city']);
            $location->setDetails($data['details']);
            $manager->persist($location);
            $this->addReference($reference, $location);
        }

        $manager->flush();
    }
}
