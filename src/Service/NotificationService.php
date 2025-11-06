<?php 
namespace App\Service;

use App\Repository\ContactRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;


class NotificationService
{
    private $contactRepository;
    private $security;
    private $entityManager;


    public function __construct(ContactRepository $contactRepository, Security $security, EntityManagerInterface $entityManager)
    {
        $this->contactRepository = $contactRepository;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function getUnreadMessageCount(): int
    {
        $user = $this->security->getUser();

         if (!$user instanceof User) { 
            return 0; 
        }

        return $this->contactRepository->countUnreadMessagesForUser($user);
    }

    public function sendMessage (User $sender, User $recipient, string $objet, string $message)
    { 
        $contact =new Contact();

        $contact->setSender($sender);
        $contact->setRecipient($recipient);
        $contact->setObjet($objet);
        $contact->setMessage($message);

        // $contact->setCreatedAt(new \DateTimeImmutable());
        $contact->setIsRead(false);

        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }
}






