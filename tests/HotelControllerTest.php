<?php

namespace App\Tests;

use App\Entity\Hotel;
use App\Repository\ClientRepository;
use App\Repository\HotelRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HotelControllerTest extends WebTestCase
{
    #[Test]
    public function when_listingHotelsAsAdmin_shouldReturn_listAllHotels(): void
    {
        $client = static::createClient();
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $hotel = $hotelRepository->findAll()[0];

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Hôtels');
        $this->assertSelectorTextContains('td:nth-child(1)', $hotel->getId());
        $this->assertSelectorTextContains('td:nth-child(2)', $hotel->getCodeHotel());
        $this->assertSelectorTextContains('td:nth-child(3)', $hotel->getNomHotel());
        $this->assertSelectorTextContains('td:nth-child(4)', str_replace("\n", ' ', $hotel->getAdresseHotel()));
        $this->assertSelectorTextContains('td:nth-child(5)', $hotel->getCategorieHotel());
    }

    #[Test]
    public function when_creatingNewHotelAsAdmin_shouldReturn_createNewHotel(): void
    {
        $client = static::createClient();
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $countBefore = $hotelRepository->count([]);
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un Hôtel');

        $newHotel = new Hotel()
            ->setCodeHotel('1234')
            ->setNomHotel('Superb hotel')
            ->setAdresseHotel('8 street of Beauty')
            ->setCategorieHotel('House')
        ;

        $client->submitForm('Enregistrer', [
            'hotel[codeHotel]' => $newHotel->getCodeHotel(),
            'hotel[nomHotel]' => $newHotel->getNomHotel(),
            'hotel[adresseHotel]' => $newHotel->getAdresseHotel(),
            'hotel[categorieHotel]' => $newHotel->getCategorieHotel(),
        ]);

        $this->assertResponseRedirects('/admin/hotel');

        $hotelDb = $hotelRepository->findOneBy([], ['id' => 'DESC']);;
        $this->assertNotNull($hotelDb, 'The hotel has not been created.');
        $this->assertEquals($newHotel->getCodeHotel(), $hotelDb->getCodeHotel());
        $this->assertEquals($newHotel->getNomHotel(), $hotelDb->getNomHotel());
        $this->assertEquals($newHotel->getAdresseHotel(), $hotelDb->getAdresseHotel());
        $this->assertEquals($newHotel->getCategorieHotel(), $hotelDb->getCategorieHotel());

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertCount($countBefore + 1, $hotelRepository->findAll());
        $this->assertEquals('admin.hotel.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_showingSpecificHotelAsAdmin_shouldReturn_showHotel(): void
    {
        $client = static::createClient();
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $id = 1;
        $hotel = $hotelRepository->findOneBy(['id' => $id]);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hôtel');
        $this->assertSelectorTextContains('tbody tr:nth-child(1) td', $hotel->getId());
        $this->assertSelectorTextContains('tbody tr:nth-child(2) td', $hotel->getCodeHotel());
        $this->assertSelectorTextContains('tbody tr:nth-child(3) td', $hotel->getNomHotel());
        $this->assertSelectorTextContains('tbody tr:nth-child(4) td', str_replace("\n", ' ', $hotel->getAdresseHotel()));
        $this->assertSelectorTextContains('tbody tr:nth-child(5) td', $hotel->getCategorieHotel());
    }

    #[Test]
    public function when_editingSpecificHotelAsAdmin_shouldReturn_editHotel(): void
    {
        $client = static::createClient();
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $id = 1;
        $client->request('GET', '/admin/hotel/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un Hôtel');

        $editedHotel = new Hotel()
            ->setCodeHotel('1234')
            ->setNomHotel('Superb hotel')
            ->setAdresseHotel('8 street of Beauty')
            ->setCategorieHotel('House')
        ;

        $client->submitForm('Modifier', [
            'hotel[codeHotel]' => $editedHotel->getCodeHotel(),
            'hotel[nomHotel]' => $editedHotel->getNomHotel(),
            'hotel[adresseHotel]' => $editedHotel->getAdresseHotel(),
            'hotel[categorieHotel]' => $editedHotel->getCategorieHotel(),
        ]);

        $this->assertResponseRedirects('/admin/hotel');

        $hotelDb = $hotelRepository->findOneBy(['id' => $id]);
        $this->assertNotNull($hotelDb, 'The hotel has not been modified.');
        $this->assertEquals($editedHotel->getCodeHotel(), $hotelDb->getCodeHotel());
        $this->assertEquals($editedHotel->getNomHotel(), $hotelDb->getNomHotel());
        $this->assertEquals($editedHotel->getAdresseHotel(), $hotelDb->getAdresseHotel());
        $this->assertEquals($editedHotel->getCategorieHotel(), $hotelDb->getCategorieHotel());

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('admin.hotel.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_deletingSpecificHotelAsAdmin_shouldReturn_deleteHotel(): void
    {
        $client = static::createClient();
        $hotelRepository = static::getContainer()->get(HotelRepository::class);
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $hotel = $hotelRepository->findOneBy([]);
        $hotelId = $hotel->getId();

        $client->request('GET', '/admin/hotel');
        $client->submitForm('Supprimer');

        $this->assertResponseRedirects('/admin/hotel');
        $deletedHotel = $hotelRepository->find($hotelId);
        $this->assertNull($deletedHotel, "The hotel {$hotelId} was not deleted.");
    }

    #[Test]
    public function when_listingHotelsAsNotConnected_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/hotel');

        $this->assertResponseRedirects('/login');
    }

    #[Test]
    public function when_showingSpecificHotelNotOwnAsClient_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();
        $clientHotelRespository = static::getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRespository->findOneByRole('ROLE_USER'));

        $client->request('GET', '/admin/hotel/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
