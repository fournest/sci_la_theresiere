<?php

namespace App\Security;



use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;



class UserChecker implements UserCheckerInterface

{
        public function checkPreAuth(UserInterface $user): void

    {

        if (!$user instanceof AppUser) {

            return;
        }

        if (!$user->IsActive()) {

            throw new CustomUserMessageAuthenticationException(

                'Le compte est désactivé! Contactez l\'administrateur pour plus d\'informations.'

            );
        }
    }

    public function checkPostAuth(UserInterface $user): void

    {

        $this->checkPreAuth($user);

        if (!$user instanceof AppUser) {
            return;
        }

        // Réactiver si inactif
        if ($user->isActive() === false) {
            $user->setIsActive(true);
            $this->em->flush();
        }
    }
}
