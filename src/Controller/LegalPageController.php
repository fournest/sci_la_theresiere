<?php

namespace App\Controller;

use App\Entity\LegalPage;
use App\Form\LegalPageType;
use App\Repository\LegalPageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;



#[Route('/legal/page')]
final class LegalPageController extends AbstractController
{
    public function __construct(private SluggerInterface $slugger)
    {
        
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route(name: 'app_legal_page_index', methods: ['GET'])]
    public function index(LegalPageRepository $legalPageRepository): Response
    {
        return $this->render('legal_page/index.html.twig', [
            'legal_pages' => $legalPageRepository->findAll(),
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_legal_page_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $legalPage = new LegalPage();
        $form = $this->createForm(LegalPageType::class, $legalPage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = $this->slugger->slug($legalPage->getTitle())->lower();
            $legalPage->setSlug($slug);
            $entityManager->persist($legalPage);
            $entityManager->flush();
            $this->addFlash('success', 'Le document légal a été créé avec succès.');

            return $this->redirectToRoute('app_legal_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('legal_page/new.html.twig', [
            'legal_page' => $legalPage,
            'form' => $form,
        ]);
    }

    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{slug}/edit', name: 'app_legal_page_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] LegalPage $legalPage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LegalPageType::class, $legalPage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le document légal a été mis à jour.');
            

            return $this->redirectToRoute('app_legal_page_index', [], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('legal_page/edit.html.twig', [
            'legal_page' => $legalPage,
            'form' => $form,
        ]);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{slug}', name: 'app_legal_page_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['slug' => 'slug'])] LegalPage $legalPage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $legalPage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($legalPage);
            $entityManager->flush();
            $this->addFlash('success', 'Le document légal a été supprimé.');
        }
        
        return $this->redirectToRoute('app_legal_page_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/{slug}', name: 'app_legal_page_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] LegalPage $legalPage): Response
    {
        return $this->render('legal_page/show.html.twig', [
            'legal_page' => $legalPage,
        ]);
    }
}
