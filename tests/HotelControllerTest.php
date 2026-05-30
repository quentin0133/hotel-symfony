<?php

namespace App\Tests;

use App\Entity\Hotel;
use App\Repository\ClientRepository;
use App\Repository\HotelRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class HotelControllerTest extends WebTestCase
{
    #[Test]
    public function when_listingHotelsAsAdmin_shouldReturn_listAllHotels(): void
    {
        $client = static::createClient();
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel');

        $id = $client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text();
        $hotel = $hotelRepository->find(trim($id));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Hôtels');
        $this->assertSelectorTextContains('tbody', $hotel->getId());
        $this->assertSelectorTextContains('tbody', $hotel->getCodeHotel());
        $this->assertSelectorTextContains('tbody', $hotel->getNomHotel());
        $this->assertSelectorTextContains('tbody', str_replace("\n", ' ', $hotel->getAdresseHotel()));
        $this->assertSelectorTextContains('tbody', $hotel->getCategorieHotel());
    }

    #[Test]
    public function when_creatingNewHotelAsAdmin_shouldReturn_createNewHotel(): void
    {
        $client = static::createClient();
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

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
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $hotel = $hotelRepository->findOneBy([]);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel/' . $hotel->getId());

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
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $hotel = $hotelRepository->findOneBy([]);
        $client->request('GET', '/admin/hotel/' . $hotel->getId() . '/edit');

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

        $hotelDb = $hotelRepository->findOneBy(['id' => $hotel->getId()]);
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
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);
        $router = $client->getContainer()->get(RouterInterface::class);

        $testAdminUser = $clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/hotel');

        $id = trim($client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text());

        $deleteUrl = $router->generate('admin.hotel.delete', ['id' => $id]);

        $form = $client->getCrawler()->filter(sprintf('form[action="%s"]', $deleteUrl))->form();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/hotel');
        $deletedHotel = $hotelRepository->findOneBy(['id' => $id]);
        $this->assertNull($deletedHotel, "The hotel {$id} was not deleted.");
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
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRespository->findOneByRole('ROLE_CLIENT'));

        $client->request('GET', '/admin/hotel/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
