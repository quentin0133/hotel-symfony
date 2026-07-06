<?php

namespace App\Controller;

use App\Repository\ChambreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the application home page and coordinates the availability search engine.
 */
final class HomeController extends AbstractController
{
    /**
     * Renders the home page and filters available rooms based on a date range.
     * @param Request           $request           The incoming HTTP request containing query parameters
     * @param ChambreRepository $chambreRepository The repository handling custom room availability queries
     * @return Response The rendered HTML home page view
     */
    #[Route('/', name: 'home.index')]
    public function index(Request $request, ChambreRepository $chambreRepository): Response
    {
        $chambresDisponibles = null;
        $dateDebutStr = $request->query->get('date_debut');
        $dateFinStr = $request->query->get('date_fin');
        $error = null;

        if ($dateDebutStr && $dateFinStr) {
            $dateDebut = \DateTime::createFromFormat('Y-m-d', $dateDebutStr);
            $dateFin = \DateTime::createFromFormat('Y-m-d', $dateFinStr);

            if (!$dateDebut || !$dateFin) {
                $error = 'Les dates saisies sont invalides.';
            } elseif ($dateFin <= $dateDebut) {
                $error = 'La date de fin doit être postérieure à la date de début.';
            } else {
                $page = $request->query->getInt('page', 1);
                $chambresDisponibles = $chambreRepository->findAvailableBetweenDates($dateDebut, $dateFin, $page);
            }
        }

        return $this->render('home/index.html.twig', [
            'chambresDisponibles' => $chambresDisponibles,
            'dateDebut' => $dateDebutStr,
            'dateFin' => $dateFinStr,
            'error' => $error,
        ]);
    }
}
