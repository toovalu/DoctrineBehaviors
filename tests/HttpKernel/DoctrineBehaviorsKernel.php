<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\HttpKernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Knp\DoctrineBehaviors\DoctrineBehaviorsBundle;
use Override;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;

final class DoctrineBehaviorsKernel extends Kernel
{
    /**
     * @param string[] $configs
     */
    public function __construct(
        private readonly array $configs = [],
    ) {
        parent::__construct('test', false);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return [new DoctrineBehaviorsBundle(), new DoctrineBundle(), new FrameworkBundle()];
    }

    #[Override]
    public function getCacheDir(): string
    {
        return \sys_get_temp_dir().'/doctrine_behaviors_test';
    }

    #[Override]
    public function getLogDir(): string
    {
        return \sys_get_temp_dir().'/doctrine_behaviors_test_log';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/../config/config_test.php');

        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
}
