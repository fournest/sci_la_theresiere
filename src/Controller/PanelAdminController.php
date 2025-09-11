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
    public function index(UserRepository $userRepository, VisiteRepository $visiteRepository, ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $limit = 5;
        $usersQuery = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->getQuery();

         $usersPagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('usersPage', 1),
            $limit,
            ['pageParameterName' => 'usersPage']
        );

        $visitesQuery = $visiteRepository->createQueryBuilder('v')
            ->orderBy('v.dateVisite', 'DESC')
            ->getQuery();

        $visitesPagination = $paginator->paginate(
            $visitesQuery,
            $request->query->getInt('visitesPage', 1),
            $limit,
            ['pageParameterName' => 'visitesPage']
        );

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
