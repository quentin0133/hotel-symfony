<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/client', name: 'admin.client.')]
final class ClientController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ClientRepository $clientRepository
    ): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->getString('search');

        $clients = $clientRepository->findByNameOrEmailLikePaginated($search, $page);

        return $this->render('admin/client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $client,
                    $plainPassword
                );

                $client->setPassword($hashedPassword);
            }

            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('admin.client.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, Client $client,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $form = $this->createForm(ClientType::class, $client, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $client,
                    $plainPassword
                );

                $client->setPassword($hashedPassword);
            }

            $entityManager->flush();

            return $this->redirectToRoute('admin.client.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.client.index', [], Response::HTTP_SEE_OTHER);
    }
}
