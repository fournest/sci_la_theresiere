<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\VisiteRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\UserType;
use App\Repository\ContactRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Knp\Component\Pager\PaginatorInterface;




#[Route('/profile')]
#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_show', methods: ['GET'])]
    public function show(
        VisiteRepository $visiteRepository,
        ReservationRepository $reservationRepository,
        ContactRepository $contactRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $limit = 5;
        $visitesQueryBuilder = $visiteRepository->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user);

        $visitesPagination = $paginator->paginate(
            $visitesQueryBuilder,
            $request->query->getInt('visites_page', 1),
            $limit,
            ['pageParameterName' => 'visites_page']
        );


        $reservationsQueryBuilder = $reservationRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user);

        $reservationsPagination = $paginator->paginate(
            $reservationsQueryBuilder,
            $request->query->getInt('reservations_page', 1),
            $limit,
            ['pageParameterName' => 'reservations_page']
        );


        $contactsQueryBuilder = $contactRepository->createQueryBuilder('c')
            ->where('c.sender = :user OR c.recipient = :user')
            ->setParameter('user', $user);

        $contactsPagination = $paginator->paginate(
            $contactsQueryBuilder,
            $request->query->getInt('contacts_page', 1),
            $limit,
            ['pageParameterName' => 'contacts_page']
        );

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'visites' => $visitesPagination,
            'reservations' => $reservationsPagination,
            'contacts' => $contactsPagination,
        ]);
    }

    #[Route('/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');


            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/desactive', name: 'app_user_desactive', methods: ['POST'])]
    public function desactive(Request $request, Security $security, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$security->isGranted('ROLE_ADMIN') && $user !== $this->getUser()) {
            throw new AccessDeniedException("Vous n'avez pas le droit de désactiver ce compte.");
        }

        if ($this->isCsrfTokenValid('desactive' . $user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(false);
            $entityManager->flush();

            if ($user === $this->getUser()) {
                $security->logout();
            }
            $this->addFlash('success', 'Le compte a été désactivé avec succès.');
            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('app_user_show');
    }

    // #[Route('/{id}/reactivate', name: 'app_user_reactivate')]
    // public function reactivate(Request $request, Security $security, User $user, EntityManagerInterface $entityManager): Response
    // {  
    //     $currentUser = $this->getUser();
    //     if (!$security->isGranted('ROLE_ADMIN') && (!$currentUser || $user->getId() !== $currentUser->getId())) {
    //         throw new AccessDeniedException("Vous n'avez pas le droit de réactiver ce compte.");
    //     }


    //     if ($this->isCsrfTokenValid('reactivate' . $user->getId(), $request->request->get('_token'))) {
    //         $user->setIsActive(true);
    //         $entityManager->flush();

    //         $this->addFlash('success', 'Votre compte a été réactivé avec succès.');
    //         return $this->redirectToRoute('app_user_show');
    //     }
    //     return $this->redirectToRoute('app_user_show');
    // }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, Security $security, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$security->isGranted('ROLE_ADMIN') && $user !== $this->getUser()) {
            throw new AccessDeniedException("Vous n'avez pas le droit de supprimer ce compte.");
        }


        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            if ($user === $this->getUser()) {
                $security->logout();
                return $this->redirectToRoute('app_logout');
            }
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'Le compte a été supprimé avec succès.');
            return $this->redirectToRoute('app_user_show');
        }
        return $this->redirectToRoute('app_user_show');
    }
}
