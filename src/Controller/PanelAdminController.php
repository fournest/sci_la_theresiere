<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\VisiteRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;





#[IsGranted('ROLE_ADMIN')]
final class PanelAdminController extends AbstractController
{
    #[Route('/panel/admin', name: 'app_panel_admin')]
    // Affichage de la page principale du panneau d'administration avec les listes des utilisateurs, visites et réservations.
    public function index(UserRepository $userRepository, VisiteRepository $visiteRepository, ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Pagination.
        // Définition du nombre d'éléments par page.
        $limit = 5;

        // Pagination des utilisateurs.
        $usersQuery = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

        $usersPagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('usersPage', 1),
            $limit,
            ['pageParameterName' => 'usersPage']
        );
        // Pagination des visites.
        $visitesQuery = $visiteRepository->createQueryBuilder('v')
            ->orderBy('v.dateVisite', 'DESC')
            ->getQuery();

        $visitesPagination = $paginator->paginate(
            $visitesQuery,
            $request->query->getInt('visitesPage', 1),
            $limit,
            ['pageParameterName' => 'visitesPage']
        );
        // Pagination des réservations.
        $reservationsQuery = $reservationRepository->createQueryBuilder('r')
            ->orderBy('r.dateResaDebut', 'DESC')
            ->getQuery();

        $reservationsPagination = $paginator->paginate(
            $reservationsQuery,
            $request->query->getInt('reservationsPage', 1),
            $limit,
            ['pageParameterName' => 'reservationsPage']
        );
        return $this->render('panel_admin/index.html.twig', [
            'controller_name' => 'PanelAdminController',

            'users' => $usersPagination,
            'visites' => $visitesPagination,
            'reservations' => $reservationsPagination,

        ]);
    }

    // Action pour bannir un utilisateur
    #[Route('/user/{id}/ban', name: 'app_user_ban', methods: ['GET'])]
    public function banUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Définition, préparation et éxecution de la mise à jour.
        $user->setIsBanned(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été banni avec succès.");

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }

    // Action pour réintégration un utilisateur
    #[Route('/user/{id}/unban', name: 'app_user_unban', methods: ['GET'])]
    public function unbanUser(User $user, EntityManagerInterface $entityManager): Response
    {
        // Définition, préparation et éxecution de la mise à jour.
        $user->setIsBanned(false);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été réintégré avec succès.");

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérification de sécurité.
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Suppression de l'utilisateur de la base de données.
            $entityManager->remove($user);
            // Enregistrement de la suppression dans la base de données.
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }
}
