<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DefaultSluggableRepositoryTest extends TestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    private $entityManager;

    private MockObject|DefaultSluggableRepository $defaultSluggableRepository;

    protected function setUp(): void
    {
        $this->defaultSluggableRepository = new DefaultSluggableRepository(
            $this->entityManager = $this->createMock(EntityManager::class)
        );
    }

    public function testIsSlugUniqueFor(): void
    {
        $sluggable = $this->createStub(SluggableInterface::class);
        $entityClass = $sluggable::class;
        $uniqueSlug = 'foobar';
        $metadata = $this->createMock(ClassMetadata::class);
        $this->entityManager->expects(self::once())
            ->method('getClassMetadata')
            ->with($entityClass)
            ->willReturn($metadata);

        $metadata->expects(self::once())
            ->method('getIdentifierValues')
            ->with($sluggable)
            ->willReturn([
                'id' => null,
                'slug' => 'foo',
                'id.id' => '123',
            ]);

        $this->entityManager->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder = $this->createMock(QueryBuilder::class));

        $queryBuilder->expects(self::once())
            ->method('select')
            ->with('COUNT(e)')
            ->willReturnSelf();

        $queryBuilder->expects(self::once())
            ->method('from')
            ->with($entityClass, 'e')
            ->willReturnSelf();

        $andWhereIndex = 0;
        $queryBuilder->expects(self::exactly(2))
            ->method('andWhere')
            ->willReturnCallback(function (string $dql) use (&$andWhereIndex, $queryBuilder): QueryBuilder {
                ++$andWhereIndex;
                self::assertContains($andWhereIndex, [1, 2]);
                self::assertSame(
                    [1 => 'e.slug = :slug', 2 => 'e.id.id != :id_id'][$andWhereIndex],
                    $dql
                );

                return $queryBuilder;
            });

        $setParameterIndex = 0;
        $queryBuilder->expects(self::exactly(2))
            ->method('setParameter')
            ->willReturnCallback(function (string $name, mixed $value) use (&$setParameterIndex, $queryBuilder, $uniqueSlug): QueryBuilder {
                ++$setParameterIndex;
                self::assertContains($setParameterIndex, [1, 2]);
                if ($setParameterIndex === 1) {
                    self::assertSame('slug', $name);
                    self::assertSame($uniqueSlug, $value);
                } else {
                    self::assertSame('id_id', $name);
                    self::assertSame('123', $value);
                }

                return $queryBuilder;
            });

        $queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query = $this->createMock(Query::class));

        $query->expects(self::once())
            ->method('getSingleScalarResult')
            ->willReturn(1);

        self::assertFalse($this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug));
    }
}
