<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;
use ReflectionClass;

#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
final class SluggableEventSubscriber
{
    /**
     * @var string
     */
    private const SLUG = 'slug';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DefaultSluggableRepository $defaultSluggableRepository,
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if (!$classMetadata->reflClass instanceof ReflectionClass) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if ($this->shouldSkip($classMetadata)) {
            return;
        }
        
        $this->addSlugMapping($classMetadata);
    }

    private function addSlugMapping(ClassMetadata $classMetadata): void
    {
        if ($classMetadata->hasField(self::SLUG)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::SLUG,
            'type' => 'string',
            'nullable' => true,
        ]);
    }   

    public function prePersist(PrePersistEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    public function preUpdate(PreUpdateEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    private function shouldSkip(ClassMetadata $classMetadata): bool
    {
        if (!\is_a($classMetadata->getName(), SluggableInterface::class, true)) {
            return true;
        }

        return $classMetadata->hasField(self::SLUG);
    }

    private function processLifecycleEventArgs(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (!$entity instanceof SluggableInterface) {
            return;
        }

        $entity->generateSlug();

        if ($entity->shouldGenerateUniqueSlugs()) {
            $this->generateUniqueSlugFor($entity);
        }
    }

    private function generateUniqueSlugFor(SluggableInterface $sluggable): void
    {
        $i = 0;
        $slug = $sluggable->getSlug();

        $uniqueSlug = $slug;

        while (!(
            $this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug)
            && $this->isSlugUniqueInUnitOfWork($sluggable, $uniqueSlug)
        )) {
            $uniqueSlug = $slug.'-'.++$i;
        }

        $sluggable->setSlug($uniqueSlug);
    }

    private function isSlugUniqueInUnitOfWork(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $scheduledEntities = $this->getOtherScheduledEntities($sluggable);
        foreach ($scheduledEntities as $scheduledEntity) {
            if ($scheduledEntity->getSlug() === $uniqueSlug) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return SluggableInterface[]
     */
    private function getOtherScheduledEntities(SluggableInterface $sluggable): array
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $uowScheduledEntities = [
            ...$unitOfWork->getScheduledEntityInsertions(),
            ...$unitOfWork->getScheduledEntityUpdates(),
            ...$unitOfWork->getScheduledEntityDeletions(),
        ];

        $scheduledEntities = [];
        foreach ($uowScheduledEntities as $uowScheduledEntity) {
            if ($uowScheduledEntity instanceof SluggableInterface && $sluggable !== $uowScheduledEntity) {
                $scheduledEntities[] = $uowScheduledEntity;
            }
        }

        return $scheduledEntities;
    }
}
