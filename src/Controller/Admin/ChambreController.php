<?php

namespace App\Controller\Admin;

use App\Entity\Chambre;
use App\Form\ChambreType;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages room entities and configuration settings within the back-office ecosystem.
 */
#[Route('/admin/chambre', name: 'admin.chambre.')]
final class ChambreController extends AbstractController
{
    /**
     * Displays a list of paginated rooms.
     */
    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        Request            $request,
        ChambreRepository  $chambreRepository
    ): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->getString('search');
        $alert = $request->query->getString('alert');

        $chambres = $chambreRepository->findByCodeChambreLikePaginated($search, $page, 10);

        return $this->render('admin/chambre/index.html.twig', [
            'chambres' => $chambres,
            'alert' => $alert
        ]);
    }

    /**
     * Creates a new room
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chambre);
            $entityManager->flush();

            $this->addFlash('success', 'Ajout de la chambre avec succès !');

            return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/new.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    /**
     * Displays a specific room.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Chambre $chambre): Response
    {
        return $this->render('admin/chambre/show.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    /**
     * Edits a room.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modification de la chambre avec succès !');

            return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/edit.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    /**
     * Delete a room.
     */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chambre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chambre);
            $entityManager->flush();

            $this->addFlash('success', 'Suppression de la chambre avec succès !');
        }

        return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
    }
}
