<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UuidableEntity;

final class UuidableTest extends AbstractBehaviorTestCase
{
    public function testUuidLoading(): void
    {
        $entity = new UuidableEntity('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();

        $this->entityManager->clear();

        $entityRepository = $this->entityManager->getRepository(UuidableEntity::class);

        /** @var UuidableInterface $entity */
        $entity = $entityRepository->find($id);

        $this->assertNotNull($entity);
    }
}
