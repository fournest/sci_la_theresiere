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





#[IsGranted('ROLE_ADMIN')]
final class PanelAdminController extends AbstractController
{
    #[Route('/panel/admin', name: 'app_panel_admin')]
    public function index(UserRepository $userRepository, VisiteRepository $visiteRepository, ReservationRepository $reservationRepository, Request $request): Response
    {
        $limit = 10;
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * $limit;

        $totalUsers = count($userRepository->findAll());
        $users = $userRepository->findBy([], [], $limit, $offset);

        $totalVisites = count($visiteRepository->findAll());
        $visites = $visiteRepository->findBy([], ['dateVisite' => 'DESC'], $limit, $offset);

        $totalReservations = count($reservationRepository->findAll());
        $reservations = $reservationRepository->findBy([], ['dateResaDebut' => 'DESC'], $limit, $offset);
        return $this->render('panel_admin/index.html.twig', [
            'controller_name' => 'PanelAdminController',

            'users' => $users,
            'totalUsersCount' => $totalUsers,

            'visites' => $visites,
            'totalVisitesCount' => $totalVisites,

            'reservations' => $reservations,
            'totalReservationsCount' => $totalReservations,



            'totalPagesUsers' => ceil($totalUsers / $limit),
            'totalPagesVisites' => ceil($totalVisites / $limit),
            'totalPagesReservations' => ceil($totalReservations / $limit),

        ]);
    }

    // Vous aurez besoin d'une action pour "bannir" un utilisateur
    #[Route('/user/{id}/ban', name: 'app_user_ban', methods: ['GET'])]
    public function banUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été bannis avec succès.");

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }

    // Vous aurez besoin d'une action pour "débannir" un utilisateur
    #[Route('/user/{id}/unban', name: 'app_user_unban', methods: ['GET'])]
    public function unbanUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(false);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', "L'utilisateur a été débannis avec succès.");

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }

    #[Route('/user/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }
}
