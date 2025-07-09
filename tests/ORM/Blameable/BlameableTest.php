<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Blameable;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Blameable\BlameableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;
use Knp\DoctrineBehaviors\Tests\Provider\EntityUserProvider;

final class BlameableTest extends AbstractBehaviorTestCase
{
    /**
     * @var EntityUserProvider
     */
    private UserProviderInterface $userProvider;

    /**
     * @var ObjectRepository<BlameableEntity>
     */
    private ObjectRepository $blameableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = $this->getService(EntityUserProvider::class);
        $this->blameableRepository = $this->entityManager->getRepository(BlameableEntity::class);
    }

    public function testCreate(): void
    {
        $blameableEntity = new BlameableEntity();

        $this->entityManager->persist($blameableEntity);
        $this->entityManager->flush();

        $userEntity = new UserEntity(1, 'user');

        $this->assertEquals($userEntity, $blameableEntity->getCreatedBy());
        $this->assertEquals($userEntity, $blameableEntity->getUpdatedBy());

        $this->assertNull($blameableEntity->getDeletedBy());
    }

    public function testUpdate(): void
    {
        $this->userProvider->prepareUserEntities();

        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        //        $this->entityManager->clear();

        $this->userProvider->changeUser('user2');

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

        // need to modify at least one column to trigger onUpdate
        $entity->setTitle('test');
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

        $createdBy = $entity->getCreatedBy();
        $this->assertInstanceOf(UserEntity::class, $createdBy);

        $this->assertEquals($createdBy->getId(), $createdBy->getId());

        $updatedBy = $entity->getUpdatedBy();
        $this->assertInstanceOf(UserEntity::class, $updatedBy);

        $this->assertSame(2, $updatedBy->getId());

        $this->assertNotSame(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testRemove(): void
    {
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        $thirdUserEntity = new UserEntity(3, 'user3');
        $this->userProvider->addUser('user3', $thirdUserEntity);
        $this->userProvider->changeUser('user3');

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertSame($thirdUserEntity, $entity->getDeletedBy());
    }

    public function testExtraSqlCalls(): void
    {
        $blameableEntity = new BlameableEntity();
        $this->entityManager->persist($blameableEntity);
        $this->entityManager->flush();

        $startKey = $this->isPostgreSql() ? 2 : 1;

        $entity = $this->blameableRepository->find($startKey);
        $this->assertSame($entity, $blameableEntity);
        
        $this->assertSame($entity->getCreatedBy(), $blameableEntity->getCreatedBy());
    }

    /**
     * @return string[]
     */
    protected function provideCustomConfigs(): array
    {
        return [__DIR__.'/../../config/config_test_with_blameable_entity.php'];
    }
}
