<?php

namespace App\Controller;

use App\Entity\CarouselImage;
use App\Form\CarouselImageType;
use App\Repository\CarouselImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[Route('/carousel/image')]

final class CarouselImageController extends AbstractController
{
    #[Route(name: 'app_carousel_image_index', methods: ['GET'])]
    public function index(CarouselImageRepository $carouselImageRepository): Response
    {
        // 1. Récupérer les images triées
        $images = $carouselImageRepository->findAllOrdered();

        return $this->render('carousel_image/index.html.twig', [
            'carousel_images' => $images,
        ]);
    }

    // --- ROUTE PUBLIQUE DE LA GALERIE ---
    // URL: /carousel
    #[Route('/carousel', name: 'app_carousel_image_public', methods: ['GET'])]
    /**
     * @param CarouselImageRepository $carouselImageRepository
     */
    public function publicShow(CarouselImageRepository $carouselImageRepository): Response
    {
        // 1. Récupérer toutes les images triées par ordre
        $images = $carouselImageRepository->findAllOrdered();

        // 2. Rendre le template de la galerie (CHANGEMENT ICI)
        return $this->render('carousel_image/public_carousel.html.twig', [
            'carousel_images' => $images,
        ]);
    }

    // Injection de SluggerInterface pour le nommage unique des fichiers
    #[Route('/new', name: 'app_carousel_image_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response // <-- SluggerInterface INJECTÉ
    {
        $carouselImage = new CarouselImage();
        $form = $this->createForm(CarouselImageType::class, $carouselImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            // Récupère le fichier uploadé (champ non mappé 'imageFile')
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                // Crée un nom de fichier unique : 'nom-de-l-image-UNIQUEID.extension'
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/carousel';

                try {
                    // Déplacement du fichier du répertoire temporaire vers public/uploads/carousel
                    $imageFile->move(
                        $targetDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier: ' . $e->getMessage());
                    return $this->redirectToRoute('app_carousel_image_new');
                }

                // Définit le nom de fichier qui sera enregistré en base de données
                $carouselImage->setFilename($newFilename);
            } else {
                $this->addFlash('error', 'Aucun fichier image n\'a été sélectionné.');
                return $this->redirectToRoute('app_carousel_image_new');
            }

            $entityManager->persist($carouselImage);
            $entityManager->flush();
            $this->addFlash('success', 'L\'image a été ajoutée avec succès.');

            return $this->redirectToRoute('app_carousel_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carousel_image/new.html.twig', [
            'carousel_image' => $carouselImage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carousel_image_show', methods: ['GET'])]
    public function show(CarouselImage $carouselImage): Response
    {
        return $this->render('carousel_image/show.html.twig', [
            'carousel_image' => $carouselImage,
        ]);
    }

    // Injection de SluggerInterface pour le nommage unique des fichiers
    #[Route('/{id}/edit', name: 'app_carousel_image_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, CarouselImage $carouselImage, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response // <-- SluggerInterface INJECTÉ
    {
        // Stocke l'ancien nom de fichier au cas où il serait remplacé
        $oldFilename = $carouselImage->getFilename();

        // Le champ 'imageFile' doit être rendu facultatif dans CarouselImageType pour l'édition
        $form = $this->createForm(CarouselImageType::class, $carouselImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            $targetDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/carousel';

            if ($imageFile) {
                // Logique de renommage et d'upload
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $targetDirectory,
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du nouveau fichier: ' . $e->getMessage());
                    return $this->redirectToRoute('app_carousel_image_edit', ['id' => $carouselImage->getId()]);
                }

                // Mettre à jour le nom dans l'entité
                $carouselImage->setFilename($newFilename);

                // Supprimer l'ancienne image
                if ($oldFilename) {
                    $filesystem = new Filesystem();
                    $filePath = $targetDirectory . '/' . $oldFilename;
                    if ($filesystem->exists($filePath)) {
                        $filesystem->remove($filePath);
                    }
                }
            }
            // Si pas de nouveau fichier ($imageFile est null), le nom de fichier existant est conservé.

            $entityManager->flush();
            $this->addFlash('success', 'L\'image a été modifiée avec succès.');

            return $this->redirectToRoute('app_carousel_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('carousel_image/edit.html.twig', [
            'carousel_image' => $carouselImage,
            'form' => $form,
        ]);
    }

    // Injection de Filesystem pour la suppression de fichier
    #[Route('/{id}', name: 'app_carousel_image_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, CarouselImage $carouselImage, EntityManagerInterface $entityManager): Response // <-- Filesystem NON INJECTÉ, on le crée dans la méthode
    {
        if ($this->isCsrfTokenValid('delete' . $carouselImage->getId(), $request->getPayload()->getString('_token'))) {

            $filename = $carouselImage->getFilename();

            // 1. Suppression de l'entrée en base de données
            $entityManager->remove($carouselImage);
            $entityManager->flush();

            // 2. Suppression du fichier physique
            if ($filename) {
                $filesystem = new Filesystem();
                $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/carousel/' . $filename;

                if ($filesystem->exists($filePath)) {
                    $filesystem->remove($filePath);
                    $this->addFlash('success', 'Image et fichier supprimés avec succès.');
                } else {
                    $this->addFlash('warning', 'Image supprimée de la base de données, mais le fichier physique était introuvable.');
                }
            } else {
                $this->addFlash('success', 'Image supprimée de la base de données (aucun fichier associé).');
            }
        }

        return $this->redirectToRoute('app_carousel_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
