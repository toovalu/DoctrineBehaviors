<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500, connection: 'default')]
final class UuidableEventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if(!($classMetadata instanceof ClassMetadata)) {
            return;
        }

        if (!\is_a($classMetadata->reflClass->getName(), UuidableInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField('uuid')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'uuid',
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $entity = $prePersistEventArgs->getObject();
        if (!$entity instanceof UuidableInterface) {
            return;
        }

        $entity->generateUuid();
    }
}
