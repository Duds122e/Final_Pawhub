<?php

namespace App\EventSubscriber;

use App\Entity\SystemLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class SecurityEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLogin',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        $log = new SystemLog();
        $log->setType('LOGIN');
        $log->setMessage("User {$user->getUsername()} logged in");
        $log->setIsRead(false);
        $log->setUser($user);

        $this->em->persist($log);
        $this->em->flush();
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        if (!$user) {
            return;
        }

        $log = new SystemLog();
        $log->setType('LOGOUT');
        $log->setMessage("User {$user->getUsername()} logged out");
        $log->setIsRead(false);
        $log->setUser($user);

        $this->em->persist($log);
        $this->em->flush();
    }
}
