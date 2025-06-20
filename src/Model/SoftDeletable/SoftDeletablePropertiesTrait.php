<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletablePropertiesTrait
{
    /**
     * @var \DateTimeInterface|null
     */
    protected $deletedAt;
}
