<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new User();
        $user->setUsername('admin');
        $user->setFirstName('Admin');
        $user->setLastName('Admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);

        $article = new Article();
        $article->setTitle('Mon Premier Article');
        $article->setContent('Contenu complet ici...');
        $article->setShortContent('Résumé...');
        $article->setEnabled(true);
        $article->setUser($user);
        $manager->persist($article);
        $manager->persist($user);

        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setUsername($this->faker->unique()->userName);
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setPassword($this->passwordHasher->hashPassword($user, 123));
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
