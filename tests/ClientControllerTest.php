<?php

namespace App\Tests;

use AllowDynamicProperties;
use App\Entity\Client as ClientHotel;
use App\Repository\ClientRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;

#[AllowDynamicProperties]
class ClientControllerTest extends WebTestCase
{
    #[Test]
    public function when_listingClientHotelAsAdmin_shouldReturn_listAllClientHotel(): void
    {
        $client = static::createClient();
        $this->clientHotelRepository = $client ->getContainer()->get(ClientRepository::class);

        $testAdminUser = $this->clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/client');

        $id = $client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text();
        $this->clientHotel = $this->clientHotelRepository->find(trim($id));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Clients');
        $this->assertSelectorTextContains('tbody', $this->clientHotel->getId());
        $this->assertSelectorTextContains('tbody', $this->clientHotel->getEmail());
        $this->assertSelectorTextContains('tbody', json_encode($this->clientHotel->getRoles()));
        $this->assertSelectorTextContains('tbody', $this->clientHotel->getCodeClient());
        $this->assertSelectorTextContains('tbody', $this->clientHotel->getNomClient());
        $this->assertSelectorTextContains('tbody', str_replace("\n", ' ', $this->clientHotel->getAdrClient()));
        $this->assertSelectorTextContains('tbody', $this->clientHotel->getTelClient());
    }


    #[Test]
    public function when_creatingNewClientHotelAsAdmin_shouldReturn_createNewClientHotel(): void
    {
        $client = static::createClient();
        $this->clientHotelRepository = $client->getContainer()->get(ClientRepository::class);
        $hasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        $testAdminUser = $this->clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $countBefore = $this->clientHotelRepository->count([]);
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/client/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter un Client');

        $newClientHotel = new ClientHotel()
            ->setEmail('michel@test.fr')
            ->setPassword('LeBgD!38')
            ->setNomClient('michel')
            ->setRoles(['ROLE_CLIENT', 'ROLE_ADMIN'])
            ->setAdrClient('8 rue des Michels')
            ->setTelClient('06.02.03.04.05')
            ->setCodeClient('michel-5')
        ;

        $form = $client->getCrawler()->selectButton('Enregistrer')->form();

        $form['client[email]'] = $newClientHotel->getEmail();
        $form['client[password]'] = $newClientHotel->getPassword();
        $form['client[codeClient]'] = $newClientHotel->getCodeClient();
        $form['client[nomClient]'] = $newClientHotel->getNomClient();
        $form['client[adrClient]'] = $newClientHotel->getAdrClient();

        $form['client[roles][1]']->tick();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/client');

        $this->clientHotelDb = $this->clientHotelRepository->findOneBy([], ['id' => 'DESC']);;
        $this->assertNotNull($this->clientHotelDb, 'The client has not been created.');
        $this->assertEquals($newClientHotel->getEmail(), $this->clientHotelDb->getEmail());
        $this->assertEqualsCanonicalizing($newClientHotel->getRoles(), $this->clientHotelDb->getRoles());
        $this->assertEquals($newClientHotel->getCodeClient(), $this->clientHotelDb->getCodeClient());
        $this->assertEquals($newClientHotel->getNomClient(), $this->clientHotelDb->getNomClient());
        $this->assertEquals($newClientHotel->getAdrClient(), $this->clientHotelDb->getAdrClient());
        $this->assertTrue(
            $hasher->isPasswordValid($this->clientHotelDb, $newClientHotel->getPassword()),
            'The password stored in the database does not match the expected hash'
        );

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertCount($countBefore + 1, $this->clientHotelRepository->findAll());
        $this->assertEquals('admin.client.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_showingSpecificClientHotelAsAdmin_shouldReturn_showClientHotel(): void
    {
        $client = static::createClient();
        $this->clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $this->clientHotel = $this->clientHotelRepository->findOneBy([]);
        $id = $this->clientHotel->getId();
        $this->clientHotel = $this->clientHotelRepository->findOneBy(['id' => $id]);

        $testAdminUser = $this->clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $client->request('GET', '/admin/client/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Client');
        $this->assertSelectorTextContains('tbody tr:nth-child(1) td', $this->clientHotel->getId());
        $this->assertSelectorTextContains('tbody tr:nth-child(2) td', $this->clientHotel->getEmail());
        $this->assertSelectorTextContains('tbody tr:nth-child(3) td', json_encode($this->clientHotel->getRoles()));
        $this->assertSelectorTextContains('tbody tr:nth-child(4) td', $this->clientHotel->getCodeClient());
        $this->assertSelectorTextContains('tbody tr:nth-child(5) td', $this->clientHotel->getNomClient());
        $this->assertSelectorTextContains('tbody tr:nth-child(6) td', str_replace("\n", ' ', $this->clientHotel->getAdrClient()));
    }

    #[Test]
    public function when_editingSpecificClientHotelAsAdmin_shouldReturn_editClientHotel(): void
    {
        $client = static::createClient();
        $this->clientHotelRepository = $client->getContainer()->get(ClientRepository::class);
        $hasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        $testAdminUser = $this->clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $this->clientHotel = $this->clientHotelRepository->findOneBy([]);
        $id = $this->clientHotel->getId();
        $client->request('GET', '/admin/client/' . $id . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un Client');

        $editedClientHotel = new ClientHotel()
            ->setEmail('michel@test.fr')
            ->setPassword('LeBgD!38')
            ->setNomClient('michel')
            ->setRoles(['ROLE_CLIENT', 'ROLE_ADMIN'])
            ->setAdrClient('8 rue des Michels')
            ->setTelClient('06.02.03.04.05')
            ->setCodeClient('michel-5')
        ;

        $form = $client->getCrawler()->selectButton('Modifier')->form();

        $form['client[email]'] = $editedClientHotel->getEmail();
        $form['client[password]'] = $editedClientHotel->getPassword();
        $form['client[codeClient]'] = $editedClientHotel->getCodeClient();
        $form['client[nomClient]'] = $editedClientHotel->getNomClient();
        $form['client[adrClient]'] = $editedClientHotel->getAdrClient();

        $form['client[roles][1]']->tick();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/client');

        $this->clientHotelDb = $this->clientHotelRepository->findOneBy(['id' => $id]);
        $this->assertNotNull($this->clientHotelDb, 'The client has not been created.');
        $this->assertEquals($editedClientHotel->getEmail(), $this->clientHotelDb->getEmail());
        $this->assertEqualsCanonicalizing($editedClientHotel->getRoles(), $this->clientHotelDb->getRoles());
        $this->assertEquals($editedClientHotel->getCodeClient(), $this->clientHotelDb->getCodeClient());
        $this->assertEquals($editedClientHotel->getNomClient(), $this->clientHotelDb->getNomClient());
        $this->assertEquals($editedClientHotel->getAdrClient(), $this->clientHotelDb->getAdrClient());
        $this->assertTrue(
            $hasher->isPasswordValid($this->clientHotelDb, $editedClientHotel->getPassword()),
            'The password stored in the database does not match the expected hash'
        );

        $client->enableProfiler();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('admin.client.index', $client->getRequest()->attributes->get('_route'));
    }

    #[Test]
    public function when_deletingSpecificClientHotelAsAdmin_shouldReturn_deleteClientHotel(): void
    {
        $client = static::createClient();
        $this->clientHotelRepository = $client->getContainer()->get(ClientRepository::class);
        $router = $client->getContainer()->get(RouterInterface::class);

        $testAdminUser = $this->clientHotelRepository->findOneByRole('ROLE_ADMIN');
        $client->loginUser($testAdminUser);

        $this->clientHotel = $this->clientHotelRepository->findOneBy([]);
        $this->clientHotelId = $this->clientHotel->getId();

        $client->request('GET', '/admin/client');

        $id = trim($client->getCrawler()->filter('tbody tr:first-child td:nth-child(1)')->text());

        $deleteUrl = $router->generate('admin.client.delete', ['id' => $id]);

        $form = $client->getCrawler()->filter(sprintf('form[action="%s"]', $deleteUrl))->form();

        $client->submit($form);


        $this->assertResponseRedirects('/admin/client');
        $deletedClientHotel = $this->clientHotelRepository->find($this->clientHotelId);
        $this->assertNull($deletedClientHotel, "The client {$this->clientHotelId} was not deleted.");
    }

    #[Test]
    public function when_listingClientHotelsAsNotConnected_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();

        $client->request('GET', '/admin/client');

        $this->assertResponseRedirects('/login');
    }

    #[Test]
    public function when_showingSpecificClientHotelNotOwnAsClient_shouldReturn_errorForbidden(): void
    {
        $client = static::createClient();
        $clientHotelRepository = $client->getContainer()->get(ClientRepository::class);

        $client->loginUser($clientHotelRepository->findOneByRole('ROLE_CLIENT'));

        $client->request('GET', '/admin/client/1');

        $this->assertResponseStatusCodeSame(403);
    }
}
