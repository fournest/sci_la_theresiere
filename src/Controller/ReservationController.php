<?php

namespace App\Controller;


use App\Entity\Reservation;
use App\Entity\User;
use App\Form\ReservationType;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Service\NotificationService;
use DateTime;
use DateTimeImmutable;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération de l'utilisateur connecté depuis le service de sécurité.
        $user = $this->getUser();
        // Vérification si l'utilisateur est bien connecté.
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        // Création d'une requête pour récupérer les réservations de l'utilisateur.
        $reservationsQuery = $reservationRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

        // Pagination des résultats de la requête.
        $reservationsPagination = $paginator->paginate(
            $reservationsQuery,
            $request->query->getInt('page', 1),
            5,
            ['pageParameterName' => 'page']
        );
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationsPagination,
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        // Création du formulaire.
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Nouvelle récupération de l'utilisateur, association à la visite créée ou ajout d'un message d'erreur si l'utilisateur est déconnecté.
            $currentUser = $this->getUser();
            if ($currentUser) {
                $reservation->setUser($currentUser);

                // Récupération des informations de la réservation pour vérification.
                $dateDebut = $reservation->getDateResaDebut();
                $dateFin = $reservation->getDateResaFin();
                $categorie = $reservation->getCategorie();

                // Vérification si la date est déjà réservée ou en attente de validation.
                if (!$reservationRepository->isRoomAvailable($categorie, $dateDebut, $dateFin)) {
                    $this->addFlash('error', 'Désolé, la salle n\'est pas disponible pour cette période.');
                    return $this->redirectToRoute('app_reservation_new');
                }
            }
            // Définition du statut, préparation et execution de l'enregistrement en base de données.
            // Création du numéro de dossier.
            $reservation->setStatut('en_attente');
            $date = new DateTime();
            $reservation->setDossierResa($date->format('Ymd'));
            $entityManager->persist($reservation);
            $entityManager->flush();
            $reservation->setDossierResa($date->format('Ymd') . $reservation->getId());
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Notification pour l'administrateur.
            $admin = $userRepository->findOneAdmin();

            if ($admin) {
                $sender = $this->getUser();
                $subject = "Nouvelle réservation en attente";
                $message = "Bonjour " . $admin->getPrenom() . ", une nouvelle réservation est en attente de votre validation.";

                $notificationService->sendMessage($sender, $admin, $subject, $message);
            }

            $this->addFlash('success', 'Votre demande de réservation a bien été envoyée. Elle est en attente de confirmation par un administrateur.');
            // Redirection de  l'utilisateur vers la liste de ses réservations après la création.
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }
        // Récupération de  toutes les réservations existantes pour les indisponibilités.
        $reservations = $reservationRepository->findAll();

        $datesIndisponibles = [];
        foreach ($reservations as $r) {
            $datesIndisponibles[] = [
                $r->getDateResaDebut()->format('Y-m-d'),
                $r->getDateResaFin()->format('Y-m-d'),
            ];
        }



        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'datesIndisponibles' => $datesIndisponibles,
        ]);
    }

    // affichage d'une visite spécifique.
    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, UserRepository $userRepository, NotificationService $notificationService): Response
    {
        // Création et soumission du formulaire.
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        //  Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Nouvelle récupération de l'utilisateur, association à la réservation créée ou ajout d'un message d'erreur si l'utilisateur est déconnecté.
            $sender = $this->getUser();
            $reservationOwner = $reservation->getUser();
            $adminUser = $userRepository->findOneAdmin();

            // Vérification que l'expéditeur et l'administrateur existent dans la base de données.
            if ($sender instanceof User && $adminUser instanceof User) {
                // Notification de l'utilisateur à l'administrateur.
                if ($sender->hasRole('ROLE_USER')) {
                    $objet = "Modification de la réservation";
                    $message = "Bonjour, une réservation a été modifiée par l'utilisateur " . $sender->getPseudo() . " .";
                    $notificationService->sendMessage($sender, $adminUser, $objet, $message);
                }
                // Notification de l'administrateur à l'utilisateur.
                elseif ($sender->hasRole('ROLE_ADMIN')) {
                    $objet = "Modification de la réservation";
                    if ($sender->getId() !== $reservationOwner->getId()) {
                        $message = "Bonjour " . $reservationOwner->getPseudo() . ", la réservation a été modifiée par l'administrateur.";

                        $notificationService->sendMessage($sender, $reservationOwner, $objet, $message);
                    }
                }
            }

            // confirmation de la modification.
            $this->addFlash('success', 'Votre réservation a été modifiée avec succès.');
            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, UserRepository $userRepository, NotificationService $notificationService): Response
    {

        // Vérification de l'existance de la réservation.
        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }
        // Vérification si l'utilisateur actuel est soit le propriétaire de la visite, soit un administrateur.
        // Si aucune de ces conditions n'est remplie, une exception AccessDeniedException est levée.
        if ($this->getUser() !== $reservation->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette réservation.');
        }

        // Sécurisation de l'action d'annulation.
        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatut('annulée');
            $entityManager->flush();
            // Nouvelle récupération de l'utilisateur, association à la visite créée ou ajout d'un message d'erreur si l'utilisateur est déconnecté.
             $sender = $this->getUser();
            $reservationOwner = $reservation->getUser();
            $adminUser = $userRepository->findOneAdmin();

            // Vérification que l'expéditeur et l'administrateur existent dans la base de données.
            if ($sender instanceof User && $adminUser instanceof User) {
                // Notification de l'utilisateur à l'administrateur.
                if ($sender->hasRole('ROLE_USER')) {
                    $objet = "Annulation de la réservation";
                    $message = "Bonjour, une réservation a été annulée par l'utilisateur " . $sender->getPseudo() . " .";
                    $notificationService->sendMessage($sender, $adminUser, $objet, $message);
                    // Notification de l'administrateur à l'utilisateur.
                } elseif ($sender->hasRole('ROLE_ADMIN')) {
                    $objet = "Annulation de la réservation";
                    if ($sender->getId() !== $reservationOwner->getId()) {
                        $message = "Bonjour " . $reservationOwner->getPseudo() . ", la réservation a été annulée par l'administrateur.";
                        $notificationService->sendMessage($sender, $reservationOwner, $objet, $message);
                    }
                }
            }
            $this->addFlash('success', 'Votre réservation a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/validate/{id}', name: 'app_reservation_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        // Vérification de l'existance de la réservation.
        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }

         // Vérification de sécurité.
        if ($this->isCsrfTokenValid('validate' . $reservation->getId(), $request->request->get('_token'))) {
            // Mise à jour du statut de la réservation.
            if ($reservation->getStatut() === 'en_attente') {
                $reservation->setStatut('validée');
                $entityManager->flush();

                 // Récupération de l'expéditeur (l'administrateur) et le destinataire (le propriétaire de la réservation)
                // et envoi de notification au destinataire.
                $sender = $this->getUser();
                $recipient = $reservation->getUser();
                $subject = "Confirmation de votre réservation";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été validée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $subject, $message);
                // Message flash pour l'administrateur.
                $this->addFlash('success', 'La réservation a été validée avec succès.');
            } else {
                $this->addFlash('error', 'La réservation n\'est pas dans un état "en_attente" et ne peut pas être validée.');
            }
        } else {
            // Message d'erreur si sécurité invalide.
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        // Vérification de l'existance de la réservation.
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            // Récupération de l'expéditeur (l'administrateur) et le destinataire (le propriétaire de la réservation)
            // et envoi de notification au destinataire.
            $sender = $this->getUser();
            $recipient = $reservation->getUser();
            $subject = "Suppression de votre réservation";
            $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été supprimée par un administrateur.";

            $notificationService->sendMessage($sender, $recipient, $subject, $message);
            $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
