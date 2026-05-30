<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Hotel;
use App\Entity\Reservation;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public const NUMBER_FIXTURES = 20;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < self::NUMBER_FIXTURES; $i++) {
            $hotel = $this->getReference("hotel_" . $faker->numberBetween(0, HotelFixtures::NUMBER_FIXTURES - 1), Hotel::class);
            $client = $this->getReference('client_' . $faker->numberBetween(0, ClientFixtures::NUMBER_FIXTURES - 1), Client::class);
            $dateDebut = $faker->dateTimeBetween('now', '+1 month');
            $reservation = new Reservation()
                ->setNumReservation($faker->slug())
                ->setDateDebut($dateDebut)
                ->setDateFin($faker->dateTimeInInterval($dateDebut->add(new DateInterval('P3D')), '+2 weeks'))
                ->setCommentaire($faker->realText())
                ->setHotel($hotel)
                ->setClient($client)
            ;


            foreach ($faker->randomElements($hotel->getChambres()) as $chambre) {
                $reservation->addChambre($chambre);
            }

            $manager->persist($reservation);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            HotelFixtures::class,
            ClientFixtures::class,
            ChambreFixtures::class
        ];
    }
}
