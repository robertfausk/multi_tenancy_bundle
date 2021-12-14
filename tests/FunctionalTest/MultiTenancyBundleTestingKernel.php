<?php
declare(strict_types=1);

namespace Hakam\MultiTenancyBundle\Tests\FunctionalTest;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Hakam\MultiTenancyBundle\HakamMultiTenancyBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class MultiTenancyBundleTestingKernel extends Kernel
{
    /**
     * @var array
     */
    private $hakamAuthenticationConfig;

    public function __construct(array $config = [])
    {
        $this->hakamAuthenticationConfig = $config;
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new HakamMultiTenancyBundle(),
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('hakam_multi_tenancy', $this->hakamAuthenticationConfig);
            $container->loadFromExtension('framework', [
                'test' => true,
            ]);
        });
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
