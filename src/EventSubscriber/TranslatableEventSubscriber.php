<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use ReflectionClass;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postLoad, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500, connection: 'default')]
final class TranslatableEventSubscriber
{
    /**
     * @var string
     */
    public const LOCALE = 'locale';

    private int $translatableFetchMode;

    private int $translationFetchMode;

    public function __construct(
        private LocaleProviderInterface $localeProvider,
        string $translatableFetchMode,
        string $translationFetchMode,
    ) {
        $this->translatableFetchMode = $this->convertFetchString($translatableFetchMode);
        $this->translationFetchMode = $this->convertFetchString($translationFetchMode);
    }

    /**
     * Adds mapping to the translatable and translations.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if (!$classMetadata->reflClass instanceof ReflectionClass) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if ($classMetadata->isMappedSuperclass) {
            return;
        }

        if (\is_a($classMetadata->reflClass->getName(), TranslatableInterface::class, true)) {
            $this->mapTranslatable($classMetadata);
        }

        if (\is_a($classMetadata->reflClass->getName(), TranslationInterface::class, true)) {
            $this->mapTranslation($classMetadata, $loadClassMetadataEventArgs->getObjectManager());
        }
    }

    public function postLoad(PostLoadEventArgs $postLoadEventArgs): void
    {
        $this->setLocales($postLoadEventArgs);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $this->setLocales($prePersistEventArgs);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::postLoad, Events::prePersist];
    }

    /**
     * Convert string FETCH mode to required string.
     */
    private function convertFetchString(string|int $fetchMode): int
    {
        if (\is_int($fetchMode)) {
            return $fetchMode;
        }

        if ($fetchMode === 'EAGER') {
            return ClassMetadata::FETCH_EAGER;
        }

        if ($fetchMode === 'EXTRA_LAZY') {
            return ClassMetadata::FETCH_EXTRA_LAZY;
        }

        return ClassMetadata::FETCH_LAZY;
    }

    private function mapTranslatable(ClassMetadata $classMetadata): void
    {
        if ($classMetadata->hasAssociation('translations')) {
            return;
        }

        $classMetadata->mapOneToMany([
            'fieldName' => 'translations',
            'mappedBy' => 'translatable',
            'indexBy' => self::LOCALE,
            'cascade' => ['persist', 'merge', 'remove'],
            'fetch' => $this->translatableFetchMode,
            'targetEntity' => $classMetadata->getReflectionClass()
                ->getMethod('getTranslationEntityClass')
                ->invoke(null),
            'orphanRemoval' => true,
        ]);
    }

    private function mapTranslation(ClassMetadata $classMetadata, ObjectManager $objectManager): void
    {
        if (!$classMetadata->hasAssociation('translatable')) {
            $targetEntity = $classMetadata->getReflectionClass()
                ->getMethod('getTranslatableEntityClass')
                ->invoke(null);

            $objectClassMetadata = $objectManager->getClassMetadata($targetEntity);

            $singleIdentifierFieldName = $objectClassMetadata->getIdentifierFieldNames()[0];

            $classMetadata->mapManyToOne([
                'fieldName' => 'translatable',
                'inversedBy' => 'translations',
                'cascade' => ['persist', 'merge'],
                'fetch' => $this->translationFetchMode,
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => $singleIdentifierFieldName,
                    'onDelete' => 'CASCADE',
                ]],
                'targetEntity' => $targetEntity,
            ]);
        }

        $name = $classMetadata->getTableName().'_unique_translation';
        if (!$this->hasUniqueTranslationConstraint($classMetadata, $name)
            && $classMetadata->getName() === $classMetadata->rootEntityName) {
            $classMetadata->table['uniqueConstraints'][$name] = [
                'columns' => ['translatable_id', self::LOCALE],
            ];
        }

        if (!$classMetadata->hasField(self::LOCALE) && !$classMetadata->hasAssociation(self::LOCALE)) {
            $classMetadata->mapField([
                'fieldName' => self::LOCALE,
                'type' => 'string',
                'length' => 5,
            ]);
        }
    }

    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (!$entity instanceof TranslatableInterface) {
            return;
        }

        $currentLocale = $this->localeProvider->provideCurrentLocale();
        if ($currentLocale) {
            $entity->setCurrentLocale($currentLocale);
        }

        $fallbackLocale = $this->localeProvider->provideFallbackLocale();
        if ($fallbackLocale) {
            $entity->setDefaultLocale($fallbackLocale);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadata $classMetadata, string $name): bool
    {
        return isset($classMetadata->table['uniqueConstraints'][$name]);
    }
}
