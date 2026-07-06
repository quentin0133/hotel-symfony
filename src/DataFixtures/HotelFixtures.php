<?php

namespace App\DataFixtures;

use App\Entity\Hotel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Generates sample data for the Hotel entity.
 * Implements dependency management to ensure valid relational mapping during seeding.
 */
class HotelFixtures extends Fixture
{
    /** The total number of hotel fixtures to generate */
    public const NUMBER_FIXTURES = 20;

    /**
     * Loads the hotel fixtures into the database.
     * @param ObjectManager $manager The Doctrine object manager responsible for persistence
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create(('fr_FR'));

        $categories = ["*", "**", "***", "****", "*****"];

        for ($i = 0; $i < self::NUMBER_FIXTURES; $i++) {
            $hotel = new Hotel()
                ->setAdresseHotel($faker->address())
                ->setNomHotel($faker->company())
                ->setCodeHotel($faker->slug())
                ->setCategorieHotel($faker->randomElement($categories));

            $this->addReference("hotel_" . $i, $hotel);
            $manager->persist($hotel);
        }

        $manager->flush();
    }
}
