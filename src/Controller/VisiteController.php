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




#[Route('/visite')]
final class VisiteController extends AbstractController
{
    #[Route(name: 'app_visite_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]

    public function index(VisiteRepository $visiteRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $visites = $visiteRepository->findBy(['user' => $user]);
        return $this->render('visite/index.html.twig', [
            'visites' => $visites,
        ]);
    }

    #[Route('/new', name: 'app_visite_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager, VisiteRepository $VisiteRepository): Response
    {
        $visite = new Visite();
        $currentUser = $this->getUser();
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $isBooked = $VisiteRepository->findValidatedBookingByDate($visite->getDateVisite());

            if ($isBooked) {
                // Étape 2 : Si elle est réservée, ajouter un message d'erreur et rediriger
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
    public function edit(Request $request, Visite $visite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VisiteType::class, $visite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('visite/edit.html.twig', [
            'visite' => $visite,
            'form' => $form,
        ]);
    }

    #[Route('/cancel/{id}', name: 'app_visite_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Visite $visite, EntityManagerInterface $entityManager): Response
    {

        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }
        // Vérifie si l'utilisateur est le propriétaire de la réservation ou un administrateur
        if ($this->getUser() !== $visite->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à annuler cette visite.');
        }

        // Vérifie si le token CSRF est valide
        if ($this->isCsrfTokenValid('cancel' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            // Change le statut de la réservation en "annulee"
            $visite->setStatut('annulée');
            $entityManager->flush();

            $this->addFlash('success', 'Votre visite a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Token de sécurité invalide.');
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/validate/{id}', name: 'app_visite_validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validate(Request $request, Visite $visite, EntityManagerInterface $entityManager): Response
    {
        if (!$visite) {
            throw new NotFoundHttpException('La visite demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('validate' . $visite->getId(), $request->request->get('_token'))) {
            if ($visite->getStatut() === 'en_attente') {
                $visite->setStatut('validée');
                $entityManager->flush();

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

    public function delete(Request $request, Visite $visite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $visite->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($visite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_visite_index', [], Response::HTTP_SEE_OTHER);
    }
}
