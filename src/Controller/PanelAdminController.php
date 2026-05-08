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

    // Correction : Passage en POST + vérification CSRF
    #[Route('/user/{id}/ban', name: 'app_user_ban', methods: ['POST'])]
    public function banUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('ban' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setIsBanned(true);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a été banni avec succès.");
        }

        return $this->redirectToRoute('app_panel_admin', ['section' => 'users']);
    }

    // Correction : Passage en POST + vérification CSRF
    #[Route('/user/{id}/unban', name: 'app_user_unban', methods: ['POST'])]
    public function unbanUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('unban' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setIsBanned(false);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a été réintégré avec succès.");
        }

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

    #[Route('/admin/promote/{id}', name: 'app_admin_promote', methods: ['POST'])]
    public function promote(User $user, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification de sécurité (le token CSRF empêche les attaques)
        if ($this->isCsrfTokenValid('promote' . $user->getId(), $request->request->get('_token'))) {

            // On donne le rôle Admin à l'utilisateur
            $user->setRoles(['ROLE_ADMIN']);
            $em->flush();

            $this->addFlash('success', $user->getPseudo() . ' est désormais Administrateur !');
        } else {
            $this->addFlash('danger', 'Erreur de sécurité lors de la promotion.');
        }

        // On redirige vers le tableau de bord (vérifie bien le nom de ta route)
        return $this->redirectToRoute('app_panel_admin');
    }
}
