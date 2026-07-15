<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route('/{url}', name: 'app_catch_all', requirements: ['url' => '.*'], priority: -100)]
    public function catchAll(Security $security): Response
    {
        throw $this->createNotFoundException('Page introuvable');
    }
}
