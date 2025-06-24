<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;

final class SoftDeletableEventSubscriber implements EventSubscriberInterface
{ 
    /**
     * @var string
     */
    private const DELETED_AT = 'deletedAt';

    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $entityManager = $onFlushEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $scheduledEntityDeletion) {
            if (!$scheduledEntityDeletion instanceof SoftDeletableInterface) {
                continue;
            }

            $oldValue = $scheduledEntityDeletion->getDeletedAt();

            $scheduledEntityDeletion->delete();
            $entityManager->persist($scheduledEntityDeletion);

            $unitOfWork->propertyChanged($scheduledEntityDeletion, self::DELETED_AT, $oldValue, $scheduledEntityDeletion->getDeletedAt());
            $unitOfWork->scheduleExtraUpdate($scheduledEntityDeletion, [
                self::DELETED_AT => [$oldValue, $scheduledEntityDeletion->getDeletedAt()],
            ]);
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (!\is_a($classMetadata->reflClass->getName(), SoftDeletableInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField(self::DELETED_AT)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::DELETED_AT,
            'type' => 'datetime',
            'nullable' => true,
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush, Events::loadClassMetadata];
    }
}
