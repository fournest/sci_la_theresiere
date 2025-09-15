<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, EntityManagerInterface $em): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {

            $user = $em->getRepository(User::class)->findOneBy(['email' => $lastUsername]);
            if ($user && $user->isBanned()) {
                $this->addFlash('error', 'Vous avez été banni par l\'administrateur, veuillez le contacter aux coordonnées ci-dessous.');
            } else {
                $this->addFlash('error', 'Identifiants invalides.');
            }
        }

        if ($request->query->get('logout') === '1') {
            $this->addFlash('success', 'Vous avez bien été déconnecté.');
        }


        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/login_success', name: 'app_login_success')]
    public function onLoginSuccess(): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $pseudo = $user->getPseudo();
            $this->addFlash('success', 'Vous êtes bien connecté en tant que ' . $pseudo . '.');
        } else {
            $this->addFlash('success', 'Vous êtes bien connecté.');
        }


        return $this->redirectToRoute('app_home');
    }



    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
