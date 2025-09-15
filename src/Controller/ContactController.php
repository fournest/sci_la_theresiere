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

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            if (!$user instanceof User) {
                $this->addFlash('error', 'Vous devez être connecté pour envoyer un message.');
                return $this->redirectToRoute('app_login');
            }

            $contact->setSender($user);

            $adminUser = $entityManager->getRepository(User::class)->findOneByRole('ROLE_ADMIN');

            if (!$adminUser) {
                $this->addFlash('error', 'Impossible de trouver un administrateur pour recevoir ce message.');
                return $this->redirectToRoute('app_contact');
            }

            $contact->setRecipient($adminUser);

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
    #[Route('/conatct/{id}', name: 'app_contact_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {

        if ($this->isCsrfTokenValid('delete' . $contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Le message a été supprimé avec success.');
        } else {
             $this->addFlash('danger', 'La suppression du message a échoué.');
        }

        return $this->redirectToRoute('app_user_show');
    }
}
