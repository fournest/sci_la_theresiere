<?php

namespace App\Controller;


use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Categorie;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;




#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    #[Route(name: 'app_reservation_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationRepository $reservationRepository, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $limit = 5;
        $page = max(1, (int) $request->query->get('page', 1));
        $offset = ($page - 1) * $limit;

        $paginationDataReservations = $reservationRepository->findPaginatedByUser($user, $limit, $offset);
        $reservations = $paginationDataReservations['reservations'];
        $totalReservations = $paginationDataReservations['totalCountReservations'];
        return $this->render('reservation/index.html.twig', [
            'currentPage' => $page,
            'reservations' => $reservations,
            'totalCountReservations' => $totalReservations,
            'totalPagesReservations' => ceil($totalReservations / $limit),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
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
    public function cancel(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {

        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }
        // Vérifie si l'utilisateur est le propriétaire de la réservation ou un administrateur
        if ($this->getUser() !== $reservation->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette réservation.');
        }

        // Vérifie si le token CSRF est valide
        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            // Change le statut de la réservation en "annulee"
            $reservation->setStatut('annulée');
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/validate/{id}', name: 'app_reservation_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if (!$reservation) {
            throw new NotFoundHttpException('La réservation demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('validate' . $reservation->getId(), $request->request->get('_token'))) {
            if ($reservation->getStatut() === 'en_attente') {
                $reservation->setStatut('validée');
                $entityManager->flush();

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
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
