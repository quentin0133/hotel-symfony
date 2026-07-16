<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Form\AdminReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages reservation entities and configuration settings within the back-office ecosystem.
 */
#[Route('/admin/reservation', name: 'admin.reservation.')]
final class ReservationController extends AbstractController
{
    /**
     * Displays a list of paginated reservations.
     */
    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        Request               $request,
        ReservationRepository $reservationRepository
    ): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->getString('search');
        $alert = $request->query->getString('alert');

        $reservations = $reservationRepository->findByNumReservationLikePaginated($search, $page);

        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
            'alert' => $alert
        ]);
    }

    /**
     * Creates a new reservation
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(AdminReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Ajout de la réservation avec succès !');

            return $this->redirectToRoute('admin.reservation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Displays a specific reservation.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Edits a reservation.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdminReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modification de la réservation avec succès !');

            return $this->redirectToRoute('admin.reservation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Delete a reservation
     */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Suppression de la réservation avec succès !');
        }

        return $this->redirectToRoute('admin.reservation.index', [], Response::HTTP_SEE_OTHER);
    }
}
