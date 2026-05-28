<?php

namespace App\Controller\Admin;

use App\Entity\Chambre;
use App\Form\ChambreType;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/chambre', name: 'admin.chambre.')]
final class ChambreController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        ChambreRepository $chambreRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $query = $chambreRepository->createQueryBuilder('c')->getQuery();
        $page = $request->query->getInt('page', 1);

        $chambres = $paginator->paginate($query, $page, 10);

        return $this->render('admin/chambre/index.html.twig', [
            'chambres' => $chambres,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chambre);
            $entityManager->flush();

            return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/new.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Chambre $chambre): Response
    {
        return $this->render('admin/chambre/show.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/chambre/edit.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chambre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chambre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.chambre.index', [], Response::HTTP_SEE_OTHER);
    }
}
