<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors;

use Knp\DoctrineBehaviors\Bundle\DependencyInjection\DoctrineBehaviorsExtension;
use Override;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineBehaviorsBundle extends Bundle
{
    #[Override]
    public function getContainerExtension(): Extension
    {
        return new DoctrineBehaviorsExtension();
    }
}
