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

/**
 * Generates sample data for the Reservation entity.
 * Implements dependency management to ensure valid relational mapping during seeding.
 */
class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    /** The total number of reservation fixtures to generate */
    public const NUMBER_FIXTURES = 20;

    /**
     * Loads the reservation fixtures into the database.
     * @param ObjectManager $manager The Doctrine object manager responsible for persistence
     */
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

    /**
     * Defines the fixtures that must be fully loaded prior to running this class.
     * @return array<int, class-string<Fixture>> The list of dependency fixture classes
     */
    public function getDependencies(): array
    {
        return [
            HotelFixtures::class,
            ClientFixtures::class,
            ChambreFixtures::class
        ];
    }
}
