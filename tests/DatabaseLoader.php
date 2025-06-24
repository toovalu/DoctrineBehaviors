<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\NullLogger;

final class DatabaseLoader
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $entityManager->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);
    }

    public function reload(): void
    {
        $classMetadataFactory = $this->entityManager->getMetadataFactory();

        $classesMetadatas = $classMetadataFactory->getAllMetadata();

        $entityClasses = [];
        foreach ($classesMetadatas as $classMetadata) {
            $entityClasses[] = $classMetadata->getName();
        }

        $this->reloadEntityClasses($entityClasses);
    }

    /**
     * @param class-string[] $entityClasses
     */
    public function reloadEntityClasses(array $entityClasses): void
    {
        $schema = [];
        foreach ($entityClasses as $entityClass) {
            $schema[] = $this->entityManager->getClassMetadata($entityClass);
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);
    }
}
