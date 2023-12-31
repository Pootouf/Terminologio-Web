<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if($error != null) {
            $this->addFlash('warning', $error->getMessage());
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(Security $security): void
    {
        $security->logout();
    }
}
