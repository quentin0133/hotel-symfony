<?php

namespace App\Controller\Client;

use App\Entity\Reservation;
use App\Form\ClientReservationType;
use App\Repository\ReservationRepository;
use App\Security\Voter\ReservationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Manages the reservation lifecycles within the client personal workspace.
 * Enforces strict ownership verification using Symfony Voters.
 */
#[Route('/client/reservation', name: 'client.reservation.')]
final class ReservationController extends AbstractController
{
    /**
     * Lists all paginated reservations belonging exclusively to the authenticated Client.
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

        $reservations = $reservationRepository->findByClientAndNumReservationLikePaginated($this->getUser(), $search, $page);

        return $this->render('client/reservation/index.html.twig', [
            'reservations' => $reservations,
            'alert' => $alert
        ]);
    }

    /**
     * Handles the creation of a new reservation, implicitly binding it to the current user.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ClientReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setClient($this->getUser());

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Ajout de la réservation avec succès !');

            return $this->redirectToRoute('client.reservation.index', ['Ajout de la réservation avec succès !'], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView()
        ]);
    }

    /**
     * Displays a specific reservation details if authorized by the Voter.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted(ReservationVoter::VIEW, subject: 'reservation')]
    public function show(Reservation $reservation): Response
    {
        return $this->render('client/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Processes changes on an existing reservation if ownership validation succeeds.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted(ReservationVoter::EDIT, subject: 'reservation')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Modification de la réservation avec succès !');

            return $this->redirectToRoute('client.reservation.index', ['Modification de la réservation avec succès !'], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView()
        ]);
    }

    /**
     * Deletes a reservation under token verification and ownership confirmation.
     */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted(ReservationVoter::DELETE, subject: 'reservation')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Suppression de la réservation avec succès !');
        }

        return $this->redirectToRoute('client.reservation.index', [], Response::HTTP_SEE_OTHER);
    }
}
