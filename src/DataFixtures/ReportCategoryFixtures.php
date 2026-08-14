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
            'report_category_inapproprie' => ['label' => 'Contenu inapproprié', 'icon' => 'bi bi-exclamation-triangle'],
            'report_category_info_erronee' => ['label' => 'Information erronée', 'icon' => 'bi bi-info-circle'],
            'report_category_arnaque' => ['label' => 'Arnaque / Spam', 'icon' => 'bi bi-shield-exclamation'],
            'report_category_annule' => ['label' => 'Événement annulé ou reporté', 'icon' => 'bi bi-calendar-x'],
            'report_category_autre' => ['label' => 'Autre', 'icon' => 'bi bi-three-dots'],
        ];

        foreach ($categories as $reference => $data) {
            $reportCategory = new ReportCategory();
            $reportCategory->setLabel($data['label']);
            $reportCategory->setIcon($data['icon']);

            $manager->persist($reportCategory);
            $this->addReference($reference, $reportCategory);
        }

        $manager->flush();
    }
}
