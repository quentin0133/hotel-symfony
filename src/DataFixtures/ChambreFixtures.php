<?php

namespace App\DataFixtures;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Enum\ChambreTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ChambreFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < HotelFixtures::NUMBER_FIXTURES; $i++) {
            $hotel = $this->getReference("hotel_" . $i, Hotel::class);
            $numberChambre = $faker->numberBetween(1, 10);

            for ($j = 0; $j < $numberChambre; $j++) {
                $chambre = new Chambre()
                    ->setCodeChambre($faker->slug())
                    ->setEtage($faker->numberBetween(0, 10))
                    ->setNombreLit($faker->numberBetween(1,4))
                    ->setType($faker->randomElement(ChambreTypeEnum::cases()))
                    ->setHotel($hotel)
                ;

                $hotel->addChambre($chambre);
                $manager->persist($chambre);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            HotelFixtures::class
        ];
    }
}
