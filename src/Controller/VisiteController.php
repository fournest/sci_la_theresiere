<?php

namespace App\Controller;

use App\Entity\Visite;
use App\Entity\User;
use App\Form\VisiteType;
use App\Repository\VisiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\NotificationService;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;






#[Route('/visite')]
final class VisiteController extends AbstractController
{
    #[Route(name: 'app_visite_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]

    public function index(VisiteRepository $visiteRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $queryBuilder = $visiteRepository->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.dateVisite', 'DESC')
            ->getQuery();


        $visitesPagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5,
            ['pageParameterName' => 'page']
        );

        return $this->render('visite/index.html.twig', [
            'visites' => $visitesPagination,
        ]);
    }

    #[Route('/new', name: 'app_visite_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, VisiteRepository $VisiteRepository, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        $visite = new Visite();
        $currentUser = $this->getUser();
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $isBooked = $VisiteRepository->findValidatedBookingByDate($visite->getDateVisite());

            if ($isBooked) {
                $this->addFlash('error', 'Cette date est en attente de validation. Choississez une autre date ou contactez-nous.');
                return $this->redirectToRoute('app_visite_new');
            }
            $currentUser = $this->getUser();
            if ($currentUser) {
                $visite->setUser($currentUser);
            } else {
                $this->addFlash('error', 'Vous devez être connecté pour créer une visite.');
                return $this->redirectToRoute('app_login');
            }

            $visite->setStatut('en_attente');
            $entityManager->persist($visite);
            $entityManager->flush();


            $admin = $userRepository->findOneAdmin();

            if ($admin) {
                $sender = $this->getUser();
                $subject = "Nouvelle visite en attente";
                $message = "Bonjour " . $admin->getPrenom() . ", une nouvelle visite est en attente de votre validation.";

                $notificationService->sendMessage($sender, $admin, $subject, $message);
            }

            return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visite/new.html.twig', [
            'visite' => $visite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_visite_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Visite $visite): Response
    {
        return $this->render('visite/show.html.twig', [
            'visite' => $visite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_visite_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Visite $visite, EntityManagerInterface $entityManager, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $sender = $this->getUser();
            $visiteOwner = $visite->getUser();
            $adminUser = $userRepository->findOneAdmin();

            if ($sender instanceof User && $adminUser instanceof User) {
                if ($sender->hasRole('ROLE_USER')) {
                    $objet = "Modification de la visite";
                    $message = "Bonjour, une visite a été modifiée par l'utilisateur " . $sender->getPseudo() . " .";
                    $notificationService->sendMessage($sender, $adminUser, $objet, $message);
                } elseif ($sender->hasRole('ROLE_ADMIN')) {
                    $objet = "Modification de la visite";
                    if ($sender->getId() !== $visiteOwner->getId()) {
                        $message = "Bonjour " . $visiteOwner->getPseudo() . ", la visite a été modifiée par l'administrateur.";
                        $notificationService->sendMessage($sender, $visiteOwner, $objet, $message);
                    }
                }
            }

            $this->addFlash('success', 'Votre visite a été modifiée avec succès.');
            return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('visite/edit.html.twig', [
            'visite' => $visite,
            'form' => $form,
        ]);
    }



    #[Route('/cancel/{id}', name: 'app_visite_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Visite $visite, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {

        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }
        if ($this->getUser() !== $visite->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette visite.');
        }

        if ($this->isCsrfTokenValid('cancel' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            $visite->setStatut('annulée');
            $entityManager->flush();

            $sender = $this->getUser();
            $recipient = $visite->getUser();

            if ($sender instanceof User && $recipient instanceof User) {
                $objet = "Annulation de votre visite";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre visite a été annulée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $objet, $message);
            }

            $this->addFlash('success', 'Votre visite a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/validate/{id}', name: 'app_visite_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request, Visite $visite, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('validate' . $visite->getId(), $request->request->get('_token'))) {
            if ($visite->getStatut() === 'en_attente') {
                $visite->setStatut('validée');
                $entityManager->flush();

                $sender = $this->getUser();
                $recipient = $visite->getUser();
                $subject = "Confirmation de votre visite";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre visite a été validée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $subject, $message);

                $this->addFlash('success', 'La visite a été validée avec succès.');
            } else {
                $this->addFlash('error', 'La visite n\'est pas dans un état "en_attente" et ne peut pas être validée.');
            }
        } else {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_visite_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function delete(Request $request, Visite $visite, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        if ($this->isCsrfTokenValid('delete' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visite);
            $entityManager->flush();

            $sender = $this->getUser();
            $recipient = $visite->getUser();
            $subject = "Suppression de votre réservation";
            $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été supprimée avec succès.";

            $notificationService->sendMessage($sender, $recipient, $subject, $message);
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }
}
