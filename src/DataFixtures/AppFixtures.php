<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Serie;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Generator $faker;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        //$this->addSeries();
        $this->ajoutUtilisateur(10);
    }



    private function ajoutUtilisateur(int $number)
    {
        $campus = $this->entityManager->getRepository(Campus::class)->findAll();


        for ($i = 0; $i < $number; $i++){

            $user = new User();

            $user
                ->setRoles(['ROLE_USER'])
                ->setUsername($this->faker->userName)
                ->setNom($this->faker->firstName)
                ->setPrenom($this->faker->lastName)
                ->setTelephone($this->faker->phoneNumber)
                ->setEmail($this->faker->email)
                ->setCampus($this->faker->randomElement($campus))
                ->setActif($this->faker->boolean());


            //utilisation du service pour encoder le mot de passe
            $password = $this->passwordHasher->hashPassword($user, '123');
            $user->setPassword($password);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

    }
}