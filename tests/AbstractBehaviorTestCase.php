<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBehaviorTestCase extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    private ContainerInterface $container;

    private ?DoctrineBehaviorsKernel $kernel = null;

    public static function setUpBeforeClass(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            self::markTestSkipped(
                'The pdo_sqlite PHP extension is required for integration tests (e.g. apt install php8.5-sqlite).'
            );
        }

        parent::setUpBeforeClass();
    }

    protected function setUp(): void
    {
        $this->kernel = new DoctrineBehaviorsKernel($this->provideCustomConfigs());
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();

        $this->entityManager = $this->getService('doctrine.orm.entity_manager');
        $this->loadDatabaseFixtures();
    }

    protected function tearDown(): void
    {
        if ($this->kernel !== null) {
            $this->kernel->shutdown();
            $this->kernel = null;
        }

        // Symfony's ErrorHandler stacks an exception handler; pop it so PHPUnit does not mark tests risky.
        restore_exception_handler();

        parent::tearDown();
    }

    protected function loadDatabaseFixtures(): void
    {
        /** @var DatabaseLoader $databaseLoader */
        $databaseLoader = $this->getService(DatabaseLoader::class);
        $databaseLoader->reload();
    }

    protected function isPostgreSql(): bool
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSQLPlatform;
    }

    /**
     * @return string[]
     */
    protected function provideCustomConfigs(): array
    {
        return [];
    }

    /**
     * @template T as object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    protected function getService(string $type): object
    {
        return $this->container->get($type);
    }
}
