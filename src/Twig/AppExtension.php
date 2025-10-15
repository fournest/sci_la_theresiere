<?php

namespace App\Twig;

use App\Service\NotificationService;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $notificationService;
    

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
       
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this->notificationService, 'getUnreadMessageCount']),
        ];
    }
}

