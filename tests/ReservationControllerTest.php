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
use function PHPUnit\Framework\assertNotNull;

class ReservationControllerTest extends WebTestCase
{
    #[Test]
    public function when_listingReservationsAsAdmin_shouldReturn_listAllReservations(): void
    {
        $client = static::createClient();
        $reservationRepository = $client->getContainer()->get(ReservationRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin/reservation');

        $id = $client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text();
        $reservation = $reservationRepository->find(trim($id));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Réservations');
        $this->assertSelectorTextContains('tbody', $reservation->getId());
        $this->assertSelectorTextContains('tbody', $reservation->getNumReservation());
        $this->assertSelectorTextContains('tbody', $reservation->getDateDebut()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getDateFin()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getCommentaire());
    }

    #[Test]
    public function when_creatingNewReservationAsAdmin_shouldReturn_createNewReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = $client->getContainer()->get(ReservationRepository::class);
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $countBefore = $reservationRepository->count([]);
        $client->loginUser($adminUser);

        $hotel = $hotelRepository->findOneBy([]);
        $chambres = array_slice($chambreRepository->findAll(), 0, 5);
        $client->request('GET', '/admin/reservation/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un Réservation');

        $newReservation = new Reservation()
            ->setNumReservation('4U74I365qT4Bd5')
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
            'admin_reservation[numReservation]' => $newReservation->getNumReservation(),
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
        $this->assertEquals($newReservation->getNumReservation(), $reservationDb->getNumReservation());
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
        $reservationRepository = $client->getContainer()->get(ReservationRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $reservation = $reservationRepository->findOneBy([]);

        $adminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $client->request('GET', '/admin/reservation/' . $reservation->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Réservation');
        $this->assertSelectorTextContains('tbody', $reservation->getId());
        $this->assertSelectorTextContains('tbody', $reservation->getNumReservation());
        $this->assertSelectorTextContains('tbody', $reservation->getDateDebut()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getDateFin()->format('d/m/Y'));
        $this->assertSelectorTextContains('tbody', $reservation->getCommentaire());
    }

    #[Test]
    public function when_editingSpecificReservationAsAdmin_shouldReturn_editReservation(): void
    {
        $client = static::createClient();
        $reservationRepository = $client->getContainer()->get(ReservationRepository::class);
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $adminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($adminUser);

        $hotel = $hotelRepository->findOneBy([]);
        $reservation = $reservationRepository->findOneBy([]);
        $chambres = array_slice($chambreRepository->findAll(), 0, 3);

        assertNotNull($reservation, 'No reservation found, the table is empty');

        $id = $reservation->getId();

        $client->request('GET', '/admin/reservation/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un Réservation');

        $editedReservation = new Reservation()
            ->setNumReservation('4U74I365qT4Bd5')
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
            'admin_reservation[numReservation]' => $editedReservation->getNumReservation(),
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
        $this->assertEquals($editedReservation->getNumReservation(), $reservationDb->getNumReservation());
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
        $reservationRepository = $client->getContainer()->get(ReservationRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);
        $router = $client->getContainer()->get(RouterInterface::class);

        $adminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
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
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRepository->findOneByRole('ROLE_CLIENT'));

        $client->request('GET', '/admin/reservation/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
