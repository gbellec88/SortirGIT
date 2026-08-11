<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //création de campus
        $campus1 = new Campus();
        $campus1->setNom("SAINT-HERBLAIN");
        $manager->persist($campus1);
        $this->addReference("campus1", $campus1);


        $campus2 = new Campus();
        $campus2->setNom("CHARTRES DE BRETAGNE");
        $manager->persist($campus2);
        $this->addReference("campus2", $campus2);

        $campus3 = new Campus();
        $campus3->setNom("LA ROCHE SUR YON");
        $manager->persist($campus3);
        $this->addReference("campus3", $campus3);

        $manager->flush();
    }
}
