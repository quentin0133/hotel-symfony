<?php

namespace App\Controller\Admin;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages hotel entities and configuration settings within the back-office ecosystem.
 */
#[Route('/admin/hotel', name: 'admin.hotel.')]
final class HotelController extends AbstractController
{
    /**
     * Displays a list of paginated hotels.
     */
    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        Request            $request,
        HotelRepository    $hotelRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $page = $request->query->getInt('page', 1);
        $search = $request->query->getString('search');

        $hotels = $hotelRepository->findByCodeHotelLikePaginated($search, $page);

        return $this->render('admin/hotel/index.html.twig', [
            'hotels' => $hotels,
        ]);
    }

    /**
     * Creates a new hotel
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();

            return $this->redirectToRoute('admin.hotel.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    /**
     * Displays a specific hotel.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('admin/hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    /**
     * Edits an hotel.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.hotel.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    /**
     * Delete an hotel
     */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.hotel.index', [], Response::HTTP_SEE_OTHER);
    }
}
