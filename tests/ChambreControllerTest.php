<?php

namespace App\Tests;

use App\Entity\Chambre;
use App\Enum\ChambreTypeEnum;
use App\Repository\ChambreRepository;
use App\Repository\ClientRepository;
use App\Repository\HotelRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ChambreControllerTest extends WebTestCase
{
    /**
     * Verifies that an administrator can access the room (chambre) listing and see the correct data displayed.
     */
    #[Test]
    public function when_listingChambresAsAdmin_shouldReturn_listAllChambres(): void
    {
        $client = static::createClient();
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/chambre');

        $id = $client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text();
        $chambre = $chambreRepository->find(trim($id));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Chambres');
        $this->assertSelectorTextContains('tbody', $chambre->getId());
        $this->assertSelectorTextContains('tbody', $chambre->getCodeChambre());
        $this->assertSelectorTextContains('tbody', $chambre->getEtage());
        $this->assertSelectorTextContains('tbody', $chambre->getType()->value);
        $this->assertSelectorTextContains('tbody', $chambre->getNombreLit());
    }

    /**
     * Ensures an administrator can successfully create a new room via the form submission.
     */
    #[Test]
    public function when_creatingNewChambreAsAdmin_shouldReturn_createNewChambre(): void
    {
        $client = static::createClient();
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);
        $hotelRepository = $client->getContainer()->get(HotelRepository::class);

        $hotel = $hotelRepository->findOneBy([]);
        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $countBefore = $chambreRepository->count([]);
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/chambre/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter une Chambre');

        $newChambre = new Chambre()
            ->setEtage(5)
            ->setNombreLit(2)
            ->setType(ChambreTypeEnum::PRESIDENTIAL_SUITE)
            ->setHotel($hotel)
        ;

        $client->submitForm('Enregistrer', [
            'chambre[etage]' => $newChambre->getEtage(),
            'chambre[nombreLit]' => $newChambre->getNombreLit(),
            'chambre[type]' => $newChambre->getType()->value,
            'chambre[hotel]' => $newChambre->getHotel()->getId(),
        ]);

        $this->assertResponseRedirects('/admin/chambre');

        $chambreDb = $chambreRepository->findOneBy([], ['id' => 'DESC']);;
        $this->assertNotNull($chambreDb, 'The chambre has not been created.');
        $this->assertEquals($newChambre->getEtage(), $chambreDb->getEtage());
        $this->assertEquals($newChambre->getNombreLit(), $chambreDb->getNombreLit());
        $this->assertEquals($newChambre->getType(), $chambreDb->getType());
        $this->assertEquals($newChambre->getHotel()->getId(), $chambreDb->getHotel()->getId());

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertCount($countBefore + 1, $chambreRepository->findAll());
        $this->assertEquals('admin.chambre.index', $client->getRequest()->attributes->get('_route'));
    }

    /**
     * Verifies that an administrator can view the detailed information of a specific room.
     */
    #[Test]
    public function when_showingSpecificChambreAsAdmin_shouldReturn_showChambre(): void
    {
        $client = static::createClient();
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $chambre = $chambreRepository->findOneBy([]);
        $id = $chambre->getId();
        $chambre = $chambreRepository->findOneBy(['id' => $id]);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/chambre/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Chambre');
        $this->assertSelectorTextContains('tbody tr:nth-child(1) td', $chambre->getId());
        $this->assertSelectorTextContains('tbody tr:nth-child(2) td', $chambre->getCodeChambre());
        $this->assertSelectorTextContains('tbody tr:nth-child(3) td', $chambre->getEtage());
        $this->assertSelectorTextContains('tbody tr:nth-child(4) td', $chambre->getType()->value);
        $this->assertSelectorTextContains('tbody tr:nth-child(5) td', $chambre->getNombreLit());
    }

    /**
     * Checks that an administrator can edit an existing room's information.
     */
    #[Test]
    public function when_editingSpecificChambreAsAdmin_shouldReturn_editChambre(): void
    {
        $client = static::createClient();
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);
        $hotelRepository = $client->getContainer()->get( HotelRepository::class);

        $hotel = $hotelRepository->findOneBy([]);
        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $chambre = $chambreRepository->findOneBy([]);
        $id = $chambre->getId();
        $client->request('GET', '/admin/chambre/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier une Chambre');

        $editedChambre = new Chambre()
            ->setEtage(5)
            ->setNombreLit(2)
            ->setType(ChambreTypeEnum::PRESIDENTIAL_SUITE)
            ->setHotel($hotel)
        ;

        $client->submitForm('Modifier', [
            'chambre[etage]' => $editedChambre->getEtage(),
            'chambre[nombreLit]' => $editedChambre->getNombreLit(),
            'chambre[type]' => $editedChambre->getType()->value,
            'chambre[hotel]' => $editedChambre->getHotel()->getId(),
        ]);

        $this->assertResponseRedirects('/admin/chambre');

        $chambreDb = $chambreRepository->findOneBy(['id' => $id]);
        $this->assertNotNull($chambreDb, 'The chambre has not been modified.');
        $this->assertEquals($editedChambre->getEtage(), $chambreDb->getEtage());
        $this->assertEquals($editedChambre->getNombreLit(), $chambreDb->getNombreLit());
        $this->assertEquals($editedChambre->getType(), $chambreDb->getType());
        $this->assertEquals($editedChambre->getHotel()->getId(), $chambreDb->getHotel()->getId());

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('admin.chambre.index', $client->getRequest()->attributes->get('_route'));
    }

    /**
     * Validates that an administrator can delete a room using the dedicated form.
     */
    #[Test]
    public function when_deletingSpecificChambreAsAdmin_shouldReturn_deleteChambre(): void
    {
        $client = static::createClient();
        $chambreRepository = $client->getContainer()->get(ChambreRepository::class);
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);
        $router = $client->getContainer()->get(RouterInterface::class);

        $testAdminUser = $clientHotelRespository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $chambre = $chambreRepository->findOneBy([]);
        $chambreId = $chambre->getId();

        $client->request('GET', '/admin/chambre');

        $id = trim($client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text());

        $deleteUrl = $router->generate('admin.chambre.delete', ['id' => $id]);

        $form = $client->getCrawler()->filter(sprintf('form[action="%s"]', $deleteUrl))->form();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/chambre');
        $deletedChambre = $chambreRepository->find($chambreId);
        $this->assertNull($deletedChambre, "The chambre {$chambreId} was not deleted.");
    }

    /**
     * Ensures anonymous users are redirected to the login page when attempting to access the admin area.
     */
    #[Test]
    public function when_listingChambresAsNotConnected_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/chambre');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Verifies that a standard client cannot access the admin room display page (HTTP 403 Forbidden).
     */
    #[Test]
    public function when_showingSpecificChambreNotOwnAsClient_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();
        $clientHotelRespository = $client->getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRespository->findOneByRole('ROLE_CLIENT'));

        $client->request('GET', '/admin/chambre/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
