<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {

    }


    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        //Participant Administrateur
        $participantAdmin = new Participant();
        $participantAdmin->setNom("BELLEC");
        $participantAdmin->setPrenom("Gaëtan");
        $participantAdmin->setEmail("gbellec@free.fr");
        $participantAdmin->setTelephone("0660965310");
        $participantAdmin->setActif(true);
        $participantAdmin->setRoles(['ROLE_ADMIN']);
        $participantAdmin->setCampus($this->getReference("campus1",Campus::class));

        $password = $this->userPasswordHasher->hashPassword($participantAdmin, '123456');
        $participantAdmin->setPassword($password);
        $manager->persist($participantAdmin);

        //création de 5 participants role ROLE_USER
        for ($i = 20; $i <= 50; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $participant->setEmail("participant$i@free.fr");
            $participant->setTelephone("06609653$i");
            $participant->setActif(true);
            $participant->setRoles(['ROLE_USER']);
            $participant->setCampus($this->getReference('campus'.mt_rand(1,3),Campus::class));
            $password = $this->userPasswordHasher->hashPassword($participant, '123456');
            $participant->setPassword($password);
            $manager->persist($participant);

        }



        $manager->flush();
    }
}
