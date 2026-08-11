<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //création de 25 sorties
        for ($i = 1; $i <= 25; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->word());

            $date = new \DateTimeImmutable();
            $datelimite = $date->modify('+30 days');

            $sortie->setDateHeureDebut($date);
            $sortie->setDuree(60);
            $sortie->setDateLimiteInscription($datelimite);
            $sortie->setNbinscriptionMax($faker->numberBetween(20, 30));
            $sortie->setInfosSortie($faker->paragraph());

            $sortie->setLieu($this->getReference('Lieu'.mt_rand(1,5),Lieu::class));
            $sortie->setEtat($this->getReference('etat'.mt_rand(1,6),Etat::class));
            $sortie->setOrganisateur($this->getReference('participant'.mt_rand(20,50),Participant::class));

            $manager->persist($sortie);
        }


        $manager->flush();
    }
}
