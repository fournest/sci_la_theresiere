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
        // Récupération de la dernière erreur d'authentification s'il y en a une.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier nom d'utilisateur (e-mail) entré par l'utilisateur.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si une erreur d'authentification s'est produite.
        if ($error) {

            // Recherche de l'utilisateur dans la base de données par son dernier nom d'utilisateur.
            $user = $em->getRepository(User::class)->findOneBy(['email' => $lastUsername]);
            // Vérification de l'existance de l'utilisateur et s'il est banni.
            if ($user && $user->isBanned()) {
                // Message spécifique.
                $this->addFlash('error', 'Vous avez été banni par l\'administrateur, veuillez le contacter aux coordonnées ci-dessous.');
            } else {
                // Message générique.
                $this->addFlash('error', 'Identifiants invalides.');
            }
        }

        // Vérification de la présence du paramètre 'logout' dans l'URL pour afficher un message de succès après la déconnexion.
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
        // Récupération de l'utilisateur actuellement connecté.
        $user = $this->getUser();

        if ($user instanceof User) {
            // Récupération du pseudo si l'utilisateur est une instance de la classe User.
            $pseudo = $user->getPseudo();
            // Message personnalisé
            $this->addFlash('success', 'Vous êtes bien connecté en tant que ' . $pseudo . '.');
        } else {
            // Message générique si l'utilisateur n'est pas une instance de la classe User.
            $this->addFlash('success', 'Vous êtes bien connecté.');
        }


        return $this->redirectToRoute('app_home');
    }


    // gérer directement par le pare_feu Symfony.
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
