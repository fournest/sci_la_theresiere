<?php

namespace App\Controller;

use App\Entity\Option;
use App\Form\OptionType;
use App\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/option')]
#[IsGranted('ROLE_ADMIN')]
final class OptionController extends AbstractController
{
    // Affichage de la liste d'options.
    #[Route(name: 'app_option_index', methods: ['GET'])]
    public function index(OptionRepository $optionRepository): Response
    {
        // Récupération de toutes les options depuis la base de données.
        return $this->render('option/index.html.twig', [
            'options' => $optionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_option_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création et soumission du formulaire.
        $option = new Option();
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

        //  Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Préparation et éxecution de la mise à jour.
            $entityManager->persist($option);
            $entityManager->flush();

            return $this->redirectToRoute('app_option_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('option/new.html.twig', [
            'option' => $option,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_option_show', methods: ['GET'])]
    public function show(Option $option): Response
    {
        return $this->render('option/show.html.twig', [
            'option' => $option,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_option_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Option $option, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire de modification, pré-rempli avec les données de l'option actuelle.
        $form = $this->createForm(OptionType::class, $option);
        $form->handleRequest($request);

         //  Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Execution de la mise à jour.
            $entityManager->flush();

            return $this->redirectToRoute('app_option_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('option/edit.html.twig', [
            'option' => $option,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_option_delete', methods: ['POST'])]
    public function delete(Request $request, Option $option, EntityManagerInterface $entityManager): Response
    {
        // Vérification de sécurité.
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->getPayload()->getString('_token'))) {
             // Préparation et éxecution de la mise à jour.
            $entityManager->remove($option);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_option_index', [], Response::HTTP_SEE_OTHER);
    }
}
