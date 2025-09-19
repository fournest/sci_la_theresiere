<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    #[IsGranted('ROLE_USER')]
    public function showMessages(ContactRepository $contactRepository, Security $security, EntityManagerInterface $entityManager): Response
    {

        // Récupération de l'utilisateur connecté depuis le service de sécurité.
        $user = $security->getUser();

        // Vérification si l'utilisateur est bien connecté.
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_home');
        }

        // Récupération de tous les messages non lus dont le destinataire est l'utilisateur actuel.
        $messages = $contactRepository->findBy([
            'recipient' => $user,
            'isRead' => false
        ]);

        // Parcours chaque message non lu.
        foreach ($messages as $message) {
            // Marquage du message comme lu.
            $message->setIsRead(true);
        }

        $entityManager->flush();

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }
}
