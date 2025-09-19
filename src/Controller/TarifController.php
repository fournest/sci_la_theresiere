<?php

namespace App\Controller;

use App\Entity\Tarif;
use App\Form\TarifType;
use App\Repository\TarifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/tarif')]
final class TarifController extends AbstractController
{
    #[Route(name: 'app_tarif_index', methods: ['GET'])]
    public function index(TarifRepository $tarifRepository): Response
    {
        return $this->render('tarif/index.html.twig', [
            'tarifs' => $tarifRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tarif_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire.
        $tarif = new Tarif();
        $form = $this->createForm(TarifType::class, $tarif);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {

            // Préparation des données.
            $entityManager->persist($tarif);
            // Enregistrement dans la base de données.
            $entityManager->flush();

            return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tarif/new.html.twig', [
            'tarif' => $tarif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tarif_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Tarif $tarif): Response
    {
        return $this->render('tarif/show.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tarif_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Tarif $tarif, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire.
        $form = $this->createForm(TarifType::class, $tarif);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement dans la base de données.
            $entityManager->flush();

            return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tarif/edit.html.twig', [
            'tarif' => $tarif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tarif_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Tarif $tarif, EntityManagerInterface $entityManager): Response
    {
        // Sécurisation de l'action de suppression.
        if ($this->isCsrfTokenValid('delete' . $tarif->getId(), $request->getPayload()->getString('_token'))) {
            // Suppression dans la base de données.
            $entityManager->remove($tarif);
            // Enregistrement de la suppression dans la base de données.
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
    }
}
