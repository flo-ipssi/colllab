<?php

// src/DataFixtures/UserFixtures.php
namespace App\DataFixtures;

use App\Entity\Material;
use App\Entity\Profession;
use App\Entity\Profile;
use App\Entity\User;
use App\Entity\UserMaterial;
use App\Entity\UserProfession;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Materials and Professions existed
        $materials = $manager->getRepository(Material::class)->findAll();
        $professions = $manager->getRepository(Profession::class)->findAll();
        
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@collab.com");
            $user->setRoles(["ROLE_USER"]);
            $user->setUsername($faker->userName);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);
            $user->setCountry($faker->country);
            $user->setCity($faker->city);
            $user->setZipCode($faker->countryCode);

            $profile = new Profile();
            $profile->setUserId($user);
            $profile->setBio("test bio");
            $profile->setAvatar("https://wallpapers-clan.com/wp-content/uploads/2023/01/rapper-style-pfp-1.jpg");

            $manager->persist($user);
            $manager->persist($profile);

            for ($k = 0; $k < rand(1, 2); $k++) {
                $userMaterial = new UserMaterial();
                $userMaterial->setUserId($user);
                $userMaterial->setUserMaterial($materials[array_rand($materials)]);
                $userMaterial->setDetails($faker->word(6));
                $manager->persist($userMaterial);
            }
            
            for ($l = 0; $l < rand(1, 2); $l++) {
                $userProfession = new UserProfession();
                $userProfession->setUser($user);
                $userProfession->setProfession($professions[array_rand($professions)]);
                $manager->persist($userProfession);
            }
        }

        $manager->flush();
    }

    
    public function getDependencies()
    {
        return [
            MaterialFixtures::class,
            ProfessionFixtures::class,
        ];
    }
}
