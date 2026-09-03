<?php

namespace App\DataFixtures;

use App\Entity\ReportCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReportCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'report_category_inapproprie' => ['label' => 'Contenu inapproprié'],
            'report_category_info_erronee' => ['label' => 'Information erronée'],
            'report_category_arnaque' => ['label' => 'Arnaque / Spam'],
            'report_category_annule' => ['label' => 'Événement annulé ou reporté'],
            'report_category_autre' => ['label' => 'Autre'],
        ];

        foreach ($categories as $reference => $data) {
            $reportCategory = new ReportCategory();
            $reportCategory->setLabel($data['label']);

            $manager->persist($reportCategory);
            $this->addReference($reference, $reportCategory);
        }

        $manager->flush();
    }
}
