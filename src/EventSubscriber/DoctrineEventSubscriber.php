<?php

namespace App\EventSubscriber;

use App\Entity\SystemLog;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class DoctrineEventSubscriber implements EventSubscriber
{
    public function __construct(private Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postInsert,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postInsert(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        // Don't log SystemLog or User creation (avoid recursion)
        if ($entity instanceof SystemLog || $entity instanceof User) {
            return;
        }

        $this->logAction('CREATE', $entity, $args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        // Don't log SystemLog updates
        if ($entity instanceof SystemLog) {
            return;
        }

        $this->logAction('UPDATE', $entity, $args);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        
        // Don't log SystemLog deletion
        if ($entity instanceof SystemLog) {
            return;
        }

        $this->logAction('DELETE', $entity, $args);
    }

    private function logAction(string $action, object $entity, LifecycleEventArgs $args): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $className = (new \ReflectionClass($entity))->getShortName();
        $id = method_exists($entity, 'getId') ? $entity->getId() : 'N/A';

        $log = new SystemLog();
        $log->setType($action);
        $log->setMessage("$action $className (ID: $id)");
        $log->setIsRead(false);
        $log->setUser($user);

        $em = $args->getObjectManager();
        $em->persist($log);
        $em->flush();
    }
}
