<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //création de 5 lieu
        for ($i = 1; $i <= 5; $i++) {
            $lieu = new Lieu();
            $lieu->setNom("Lieu $i");
            $lieu->setVille($faker->city());
            $lieu->setRue($faker->streetAddress());
            $lieu->setCodePostal($faker->numerify('#####'));
            $lieu->setLattitude($faker->latitude($min = -90, $max = 90));
            $lieu->setLongitude($faker->longitude($min = -180, $max = 180));
            $manager->persist($lieu);
            $this->addReference("Lieu$i", $lieu);

        }

        $manager->flush();
    }
}
