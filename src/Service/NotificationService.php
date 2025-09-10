<?php 
namespace App\Service;

use App\Repository\ContactRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;


class NotificationService
{
    private $contactRepository;
    private $security;

    public function __construct(ContactRepository $contactRepository, Security $security)
    {
        $this->contactRepository = $contactRepository;
        $this->security = $security;
    }

    public function getUnreadMessageCount(): int
    {
        $user = $this->security->getUser();

         if (!$user instanceof User) { 
            return 0; 
        }

        return $this->contactRepository->countUnreadMessagesForUser($user);
    }
}












