<?php

namespace App\DataFixtures;

use \Faker\Factory;
use App\Entity\Film;
// use App\Entity\User;
use App\Entity\Director;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    // private $hasher;

    public function __construct(){
        // $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $listDirector = [];
        
        // $user = new User();
        // $user->setPseudo($faker->userName());
        // $user->setEmail("fred.gruwe@gmail.com");
        // $user->setRoles(["ROLE_USER"]);
        // $user->setPassword($this->hasher->hashPassword($user, "password"));
        // $manager->persist($user);

        // $userAdmin = new User();
        // $userAdmin->setPseudo($faker->userName());
        // $userAdmin->setEmail("fred.gruwe@laposte.net");
        // $userAdmin->setROles(["ROLE_ADMIN", "ROLE_USER"]);
        // $userAdmin->setPassword($this->hasher->hashPassword($userAdmin, "password"));
        // $manager->persist($userAdmin);


        for ($index = 0; $index < 20; $index++){
            $director = new Director();
            $director->setLastName($faker->name());
            $manager->persist($director);
            $listDirector[] = $director;
            
        }

        for ($index = 0; $index < 20; $index++){
            $film = new Film();
            $film->setTitle($faker->name());
            $film->setDescription($faker->text());
            $film->setPhoto($faker->text());
            $film->setDirector($listDirector[array_rand($listDirector)]);
            $manager->persist($film);
        }

       

        $manager->flush();
    }


}
