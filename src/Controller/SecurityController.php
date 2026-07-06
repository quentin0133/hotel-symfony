<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Handles security-related actions such as user authentication (login) and session termination (logout).
 */
class SecurityController extends AbstractController
{
    /**
     * Renders the login form and processes authentication errors.
     * @param AuthenticationUtils $authenticationUtils Symfony utility helper to retrieve security metadata
     * @return Response The rendered HTML login page view
     */
    #[Route(path: '/login', name: 'security.login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Entry point for the logout route.
     * @throws \LogicException If the firewall configuration fails to intercept the request
     */
    #[Route(path: '/logout', name: 'security.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
