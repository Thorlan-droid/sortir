<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\User;
use App\Entity\Ville;
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
        $this->ajoutCampus(3);
        $this->ajoutVille(3);
        $this->ajoutUtilisateur(10);

    }

    private function ajoutVille(int $number){

        $ville = new Ville();

        for ($i = 0; $i < $number; $i++) {

            $ville->setNom($this->faker->city);
            $ville->setCodePostal($this->faker->postcode);


            $this->entityManager->persist($ville);
        }

        $this->entityManager->flush();

    }

    private function ajoutCampus(int $number){

        $campus = new Campus();

        for ($i = 0; $i < $number; $i++) {

            $campus->setNom($this->faker->city);

            $this->entityManager->persist($campus);
        }

        $this->entityManager->flush();

    }



    private function ajoutUtilisateur(int $number)
    {
        $campus = $this->entityManager->getRepository(Campus::class)->findAll();


        for ($i = 0; $i < $number; $i++){

            $user = new User();

            $user
                ->setRoles((['ROLE_USER']))
                ->setUsername($this->faker->userName)
                ->setNom($this->faker->lastName)
                ->setPrenom($this->faker->firstName)
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

    private function addLieu(ObjectManager $manager)
    {
        $villerepo=new VilleRepository($this->managerRegistry);
        $villes=$villerepo->findAll();

        for ($i=0; $i<50; $i++){

            $lieu = new Lieu();

            $lieu->setVille($this->faker->randomElement($villes));
            $lieu->setNom($this->faker->city);
            $lieu->setRue($this->faker->address);
            $lieu->setLatitude($this->faker->latitude);
            $lieu->setLongitude($this->faker->longitude);

            $manager->persist($lieu);
        }
        $manager->flush();
    }
}