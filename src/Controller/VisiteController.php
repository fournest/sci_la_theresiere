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
        // Récupère l'utilisateur connecté depuis le service de sécurité
        $user = $this->getUser();
        // Vérification si l'utilisateur est bien connecté.
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        // Création d'une requête pour récupérer les visites de l'utilisateur.
        $queryBuilder = $visiteRepository->createQueryBuilder('v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.dateVisite', 'DESC')
            ->getQuery();

        // Pagination des résultats de la requête.
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
    public function new(Request $request, EntityManagerInterface $entityManager, VisiteRepository $visiteRepository, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        // Création du formulaire.
        $visite = new Visite();
        $currentUser = $this->getUser();
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification si la date est déjà réservée ou en attente de validation.
            $isBooked = $visiteRepository->findValidatedBookingByDate($visite->getDateVisite());

            if ($isBooked) {
                $this->addFlash('error', 'Cette date est en attente de validation. Choississez une autre date ou contactez-nous.');
                return $this->redirectToRoute('app_visite_new');
            }
            // Nouvelle récupération de l'utilisateur, association à la visite créée ou ajout d'un message d'erreur si l'utilsateur est déconnecté.
            $currentUser = $this->getUser();
            if ($currentUser) {
                $visite->setUser($currentUser);
            } else {
                $this->addFlash('error', 'Vous devez être connecté pour créer une visite.');
                return $this->redirectToRoute('app_login');
            }
            // Définition du statut, préparation et execution de l'enregistrement en base de données.
            $visite->setStatut('en_attente');
            $entityManager->persist($visite);
            $entityManager->flush();

            // Notification pour l'administrateur.
            $admin = $userRepository->findOneAdmin();

            if ($admin) {
                $sender = $this->getUser();
                $subject = "Nouvelle visite en attente";
                $message = "Bonjour " . $admin->getPrenom() . ", une nouvelle visite est en attente de votre validation.";

                $notificationService->sendMessage($sender, $admin, $subject, $message);
            }
            // Redirection de  l'utilisateur vers la liste de ses visites après la création.
            return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
        }
        // Gestion des dates de visites indisponibles.
        $visites = $visiteRepository->findAll();

        $datesIndisponibles = [];
        foreach ($visites as $v) {
            $datesIndisponibles[] = $v->getDateVisite()->format('Y-m-d');
        }

        return $this->render('visite/new.html.twig', [
            'visite' => $visite,
            'form' => $form,
            'datesIndisponibles' => $datesIndisponibles,
        ]);
    }
    // affichage d'une visite spécifique.
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
        // Création et soumission du formulaire.
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        //  Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Nouvelle récupération de l'utilisateur, association à la visite créée ou ajout d'un message d'erreur si l'utilisateur est déconnecté.
            $sender = $this->getUser();
            $visiteOwner = $visite->getUser();
            $adminUser = $userRepository->findOneAdmin();

            // Vérification que l'expéditeur et l'administrateur existent dans la base de données.
            if ($sender instanceof User && $adminUser instanceof User) {
                // Notification de l'utilisateur à l'administrateur.
                if ($sender->hasRole('ROLE_USER')) {
                    $objet = "Modification de la visite";
                    $message = "Bonjour, une visite a été modifiée par l'utilisateur " . $sender->getPseudo() . " .";
                    $notificationService->sendMessage($sender, $adminUser, $objet, $message);
                }
                // Notification de l'administrateur à l'utilisateur.
                elseif ($sender->hasRole('ROLE_ADMIN')) {
                    $objet = "Modification de la visite";
                    if ($sender->getId() !== $visiteOwner->getId()) {
                        $message = "Bonjour " . $visiteOwner->getPseudo() . ", la visite a été modifiée par l'administrateur.";
                        $notificationService->sendMessage($sender, $visiteOwner, $objet, $message);
                    }
                }
            }
            // confirmation de la modification.
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
    public function cancel(Request $request, Visite $visite, EntityManagerInterface $entityManager, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        // Vérification de l'existance de la visite.
        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }
        // Vérification si l'utilisateur actuel est soit le propriétaire de la visite, soit un administrateur.
        // Si aucune de ces conditions n'est remplie, une exception AccessDeniedException est levée.
        if ($this->getUser() !== $visite->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette visite.');
        }
        // Sécurisation de l'action d'annulation.
        if ($this->isCsrfTokenValid('cancel' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            $visite->setStatut('annulée');
            $entityManager->flush();
            // Nouvelle récupération de l'utilisateur, association à la visite créée ou ajout d'un message d'erreur si l'utilsateur est déconnecté.
            $sender = $this->getUser();
            $visiteOwner = $visite->getUser();
            $adminUser = $userRepository->findOneAdmin();

            // Vérification que l'expéditeur et l'administrateur existent dans la base de données.
            if ($sender instanceof User && $adminUser instanceof User) {
                // Notification de l'utilisateur à l'administrateur.
                if ($sender->hasRole('ROLE_USER')) {
                    $objet = "Annulation de la visite";
                    $message = "Bonjour, une visite a été annulée par l'utilisateur " . $sender->getPseudo() . " .";
                    $notificationService->sendMessage($sender, $adminUser, $objet, $message);
                    // Notification de l'administrateur à l'utilisateur.
                } elseif ($sender->hasRole('ROLE_ADMIN')) {
                    $objet = "Annulation de la visite";
                    if ($sender->getId() !== $visiteOwner->getId()) {
                        $message = "Bonjour " . $visiteOwner->getPseudo() . ", la visite a été annulée par l'administrateur.";
                        $notificationService->sendMessage($sender, $visiteOwner, $objet, $message);
                    }
                }
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
        // Vérification de l'existance de la visite.
        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }

        // Vérification de sécurité.
        if ($this->isCsrfTokenValid('validate' . $visite->getId(), $request->request->get('_token'))) {
            // Mise à jour du statut de la visite.
            if ($visite->getStatut() === 'en_attente') {
                $visite->setStatut('validée');
                $entityManager->flush();

                // Récupération de l'expéditeur (l'administrateur) et le destinataire (le propriétaire de la visite)
                // et envoi de notification au destinataire.
                $sender = $this->getUser();
                $recipient = $visite->getUser();
                $subject = "Confirmation de votre visite";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre visite a été validée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $subject, $message);
                // Message flash pour l'administrateur.
                $this->addFlash('success', 'La visite a été validée avec succès.');
            } else {
                $this->addFlash('error', 'La visite n\'est pas dans un état "en_attente" et ne peut pas être validée.');
            }
        } else {
            // Message d'erreur si sécurité invalide.
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_visite_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]

    public function delete(Request $request, Visite $visite, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        // Vérification de l'existance de la visite.
        if ($this->isCsrfTokenValid('delete' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visite);
            $entityManager->flush();

            // Récupération de l'expéditeur (l'administrateur) et le destinataire (le propriétaire de la visite)
            // et envoi de notification au destinataire.
            $sender = $this->getUser();
            $recipient = $visite->getUser();
            $subject = "Suppression de votre réservation";
            $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été supprimée avec succès.";

            $notificationService->sendMessage($sender, $recipient, $subject, $message);
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }
}
