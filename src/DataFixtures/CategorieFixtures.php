<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            'category_concert' => 'Concert',
            'category_festival' => 'Festival',
            'category_showcase' => 'Showcase',
            'category_gratuit' => 'Gratuit',
            'category_theatre' => 'Théâtre',
        ];

        foreach ($data as $reference => $name) {
            $category = new Categorie();
            $category->setName($name);
            $manager->persist($category);
            $this->addReference($reference, $category);
        }

        $manager->flush();
    }
}
