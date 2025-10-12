<?php

namespace App\Controller;

use App\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire.
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de l'utilisateur connecté.
            $user = $this->getUser();

            // Association de l'utilisateur connecté comme expéditeur du message.
            $contact->setSender($user);

            // Recherche d'un utilisateur avec le rôle 'ROLE_ADMIN' dans la base de données.
            $adminUser = $entityManager->getRepository(User::class)->findOneByRole('ROLE_ADMIN');

            // Vérification si l'administrateur a été trouvé.
            if (!$adminUser) {
                $this->addFlash('error', 'Impossible de trouver un administrateur pour recevoir ce message.');
                return $this->redirectToRoute('app_contact');
            }

            // Association de l'administrateur comme destinataire du message.
            $contact->setRecipient($adminUser);

            // Préparation et éxecution de l'enregistrement.
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre message a été envoyé avec succès !'
            );

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
    #[Route('/contact/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {

        $currentUser = $this->getUser();

        if ($currentUser !== $contact->getSender() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à supprimer ce message.');
            return $this->redirectToRoute('app_user_show');
        }
        // Vérification de la soumission et de la validation du formulaire.
        if ($this->isCsrfTokenValid('delete' . $contact->getId(), $request->request->get('_token'))) {
            // Préparation et éxecution de l'enregistrement.
            $entityManager->remove($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Le message a été supprimé avec success.');
        } else {
            $this->addFlash('danger', 'La suppression du message a échoué.');
        }

        return $this->redirectToRoute('app_user_show');
    }
}
