<?php

namespace App\DataFixtures;

use App\Entity\Hotel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class HotelFixtures extends Fixture
{
    public const NUMBER_FIXTURES = 20;

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
