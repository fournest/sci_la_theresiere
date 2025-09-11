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
use Knp\Component\Pager\PaginatorInterface;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

       $reservationsQuery = $reservationRepository->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery();

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
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();
            if ($currentUser) {
                $reservation->setUser($currentUser);

                $dateDebut = $reservation->getDateResaDebut();
                $dateFin = $reservation->getDateResaFin();
                $categorie = $reservation->getCategorie();

                if (!$reservationRepository->isRoomAvailable($categorie, $dateDebut, $dateFin)) {
                    $this->addFlash('error', 'Désolé, la salle n\'est pas disponible pour cette période.');
                    return $this->redirectToRoute('app_reservation_new');
                }
            }
            $reservation->setStatut('en_attente');
            $entityManager->persist($reservation);
            $entityManager->flush();

            $admin = $userRepository->findOneBy(['roles' => '["ROLE_ADMIN"]']);

            if ($admin) {
                $sender = $this->getUser();
                $subject = "Nouvelle réservation en attente";
                $message = "Bonjour " . $admin->getPrenom() . ", une nouvelle réservation est en attente de votre validation.";

                $notificationService->sendMessage($sender, $admin, $subject, $message);
            }

            $this->addFlash('success', 'Votre demande de réservation a bien été envoyée. Elle est en attente de confirmation par un administrateur.');

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

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
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {

        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }
        if ($this->getUser() !== $reservation->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette réservation.');
        }

        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $reservation->setStatut('annulée');
            $entityManager->flush();

            $sender = $this->getUser();
            $recipient = $reservation->getUser();

            if ($sender instanceof User && $recipient instanceof User) {
                $objet = "Annulation de votre réservation";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été annulée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $objet, $message);
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
        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('validate' . $reservation->getId(), $request->request->get('_token'))) {
            if ($reservation->getStatut() === 'en_attente') {
                $reservation->setStatut('validée');
                $entityManager->flush();

                $sender = $this->getUser();
                $recipient = $reservation->getUser();
                $subject = "Confirmation de votre réservation";
                $message = "Bonjour " . $recipient->getPrenom() . ", votre réservation a été validée avec succès.";

                $notificationService->sendMessage($sender, $recipient, $subject, $message);



                $this->addFlash('success', 'La réservation a été validée avec succès.');
            } else {
                $this->addFlash('error', 'La réservation n\'est pas dans un état "en_attente" et ne peut pas être validée.');
            }
        } else {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

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
