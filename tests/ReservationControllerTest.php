<?php

namespace App\Tests;

use App\Entity\Reservation;
use App\Repository\ChambreRepository;
use App\Repository\ClientRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ReservationControllerTest extends WebTestCase
{
    #[Test]
    public function when_listingReservationsAsAdmin_shouldReturn_listAllReservations(): void
    {
        $client = static::createClient();
        $reservationRepository = static::getContainer()->get(ReservationRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin/reservation');

        $id = $client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text();
        $reservation = $reservationRepository->find(trim($id));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Réservations');
        $this->assertSelectorTextContains('tbody', $reservation->getId());
        $this->assertSelectorTextContains('tbody', $reservation->getDateDebut()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getDateFin()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getCommentaire());
    }

    #[Test]
    public function when_creatingNewReservationAsAdmin_shouldReturn_createNewReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = static::getContainer()->get(ReservationRepository::class);
        $chambreRepository = static::getContainer()->get(ChambreRepository::class);
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $countBefore = $reservationRepository->count([]);
        $client->loginUser($adminUser);

        $hotel = $hotelRepository->findOneBy([]);
        $chambres = array_slice($chambreRepository->findAll(), 0, 5);
        $client->request('GET', '/admin/reservation/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un Réservation');

        $newReservation = new Reservation()
            ->setDateDebut(new \DateTime('2020-01-01'))
            ->setDateFin(new \DateTime('2020-01-02'))
            ->setCommentaire(null)
            ->setClient($adminUser)
            ->setHotel($hotel)
        ;

        for ($i = 0; $i < count($chambres); $i++) {
            $newReservation->addChambre($chambres[$i]);
        }

        $client->submitForm('Enregistrer', [
            'admin_reservation[dateDebut]' => $newReservation->getDateDebut()->format('Y-m-d'),
            'admin_reservation[dateFin]' => $newReservation->getDateFin()->format('Y-m-d'),
            'admin_reservation[commentaire]' => $newReservation->getCommentaire(),
            'admin_reservation[client]' => $newReservation->getClient()->getId(),
            'admin_reservation[hotel]' => $newReservation->getHotel()->getId(),
            'admin_reservation[chambres]' => $newReservation->getChambres()->map(fn($c) => $c->getId())->toArray(),
        ]);

        $this->assertResponseRedirects('/admin/reservation');

        $reservationDb = $reservationRepository->findOneBy([], ['id' => 'DESC']);

        $this->assertNotNull($reservationDb, 'The reservation has not been created.');
        $this->assertEquals($newReservation->getDateDebut(), $reservationDb->getDateDebut());
        $this->assertEquals($newReservation->getDateFin(), $reservationDb->getDateFin());
        $this->assertEquals($newReservation->getCommentaire(), $reservationDb->getCommentaire());
        $this->assertEquals($newReservation->getClient()->getId(), $reservationDb->getClient()->getId());
        $this->assertEquals($newReservation->getHotel()->getId(), $reservationDb->getHotel()->getId());

        $expectedIds = array_values($newReservation->getChambres()->map(fn($c) => $c->getId())->toArray());
        $actualIds = array_values($reservationDb->getChambres()->map(fn($c) => $c->getId())->toArray());

        foreach ($expectedIds as $expectedId) {
            $this->assertContains(
                $expectedId,
                $actualIds,
                "The chambre with the ID $expectedId was not found in the database reservation"
            );
        }

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertCount($countBefore + 1, $reservationRepository->findAll());
        $this->assertEquals('admin.reservation.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_showingSpecificReservationAsAdmin_shouldReturn_showReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = static::getContainer()->get(ReservationRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $id = 1;
        $reservation = $reservationRepository->findOneBy(['id' => $id]);

        $adminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin/reservation/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Réservation');
        $this->assertSelectorTextContains('tbody tr:nth-child(1) td', $reservation->getId());
        $this->assertSelectorTextContains('tbody tr:nth-child(2) td', $reservation->getDateDebut()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody tr:nth-child(3) td', $reservation->getDateFin()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody tr:nth-child(4) td', $reservation->getCommentaire());
    }

    #[Test]
    public function when_editingSpecificReservationAsAdmin_shouldReturn_editReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = static::getContainer()->get(ReservationRepository::class);
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $chambreRepository = static::getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $id = 1;
        $hotel = $hotelRepository->findOneBy([]);
        $chambres = array_slice($chambreRepository->findAll(), 0, 3);
        $client->request('GET', '/admin/reservation/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un Réservation');

        $editedReservation = new Reservation()
            ->setDateDebut(new \DateTime('2020-01-01'))
            ->setDateFin(new \DateTime('2020-01-02'))
            ->setCommentaire(null)
            ->setClient($adminUser)
            ->setHotel($hotel)
        ;

        for ($i = 0; $i < count($chambres); $i++) {
            $editedReservation->addChambre($chambres[$i]);
        }

        $client->submitForm('Modifier', [
            'admin_reservation[dateDebut]' => $editedReservation->getDateDebut()->format('Y-m-d'),
            'admin_reservation[dateFin]' => $editedReservation->getDateFin()->format('Y-m-d'),
            'admin_reservation[commentaire]' => $editedReservation->getCommentaire(),
            'admin_reservation[client]' => $editedReservation->getClient()->getId(),
            'admin_reservation[hotel]' => $editedReservation->getHotel()->getId(),
            'admin_reservation[chambres]' => $editedReservation->getChambres()->map(fn($c) => $c->getId())->toArray(),
        ]);

        $this->assertResponseRedirects('/admin/reservation');

        $reservationDb = $reservationRepository->findOneBy(['id' => $id]);
        $this->assertNotNull($reservationDb, 'The reservation has not been modified.');
        $this->assertEquals($editedReservation->getDateDebut()->format('Y-m-d'), $reservationDb->getDateDebut()->format('Y-m-d'));
        $this->assertEquals($editedReservation->getDateFin()->format('Y-m-d'), $reservationDb->getDateFin()->format('Y-m-d'));
        $this->assertEquals($editedReservation->getCommentaire(), $reservationDb->getCommentaire());
        $this->assertEquals($editedReservation->getHotel()->getId(), $reservationDb->getHotel()->getId());
        $this->assertEquals($editedReservation->getClient()->getId(), $reservationDb->getClient()->getId());

        $expectedIds = array_values($editedReservation->getChambres()->map(fn($c) => $c->getId())->toArray());
        $actualIds = array_values($reservationDb->getChambres()->map(fn($c) => $c->getId())->toArray());

        foreach ($expectedIds as $expectedId) {
            $this->assertContains(
                $expectedId,
                $actualIds,
                "The chambre with the ID $expectedId was not found in the database reservation"
            );
        }

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('admin.reservation.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_deletingSpecificReservationAsAdmin_shouldReturn_deleteReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = static::getContainer()->get(ReservationRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);
        $router = static::getContainer()->get(RouterInterface::class);

        $adminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin/reservation');

        $id = trim($client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text());

        $deleteUrl = $router->generate('admin.reservation.delete', ['id' => $id]);

        $form = $client->getCrawler()->filter(sprintf('form[action="%s"]', $deleteUrl))->form();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/reservation');
        $deletedReservation = $reservationRepository->find($id);
        $this->assertNull($deletedReservation, "The reservation {$id} was not deleted.");
    }

    #[Test]
    public function when_listingReservationsAsNotConnected_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/reservation');

        $this->assertResponseRedirects('/login');
    }

    #[Test]
    public function when_showingSpecificReservationNotOwnAsClient_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRespository->findOneByRole('ROLE_USER'));

        $client->request('GET', '/admin/reservation/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
