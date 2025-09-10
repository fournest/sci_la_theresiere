<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security; 
use App\Entity\User; 

final class MessageController extends AbstractController
{
    #[Route('/messages', name: 'app_messages')]
    public function showMessages( ContactRepository $contactRepository, Security $security, EntityManagerInterface $entityManager ): Response {
        $user = $security->getUser();
        
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_home');
        }

        $messages = $contactRepository->findBy([
            'recipient' => $user,
            'isRead' => false
        ]);

        foreach ($messages as $message) {
            $message->setIsRead(true); 
        }

        $entityManager->flush(); 

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }

}
