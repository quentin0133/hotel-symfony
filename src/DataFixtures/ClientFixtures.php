<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Generates sample data for the Client entity.
 * Implements dependency management to ensure valid relational mapping during seeding.
 */
class ClientFixtures extends Fixture
{
    /** The total number of client fixtures to generate */
    public const NUMBER_FIXTURES = 10;

    /**
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    /**
     * Loads the client fixtures into the database.
     * @param ObjectManager $manager The Doctrine object manager responsible for persistence
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < self::NUMBER_FIXTURES; $i++) {
            $user = new Client();
            $user
                ->setRoles([$i === 0 ? 'ROLE_ADMIN' : 'ROLE_CLIENT'])
                ->setNomClient($faker->name())
                ->setAdrClient($faker->address())
                ->setCodeClient($faker->slug())
                ->setEmail($faker->email())
                ->setTelClient($faker->phoneNumber())
                ->setPassword($this->hasher->hashPassword($user, '1234'))
            ;
            $this->addReference('client_' . $i, $user);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
