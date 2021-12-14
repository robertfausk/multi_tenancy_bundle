<?php
declare(strict_types=1);

namespace Hakam\MultiTenancyBundle\Tests\FunctionalTest;

use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Hakam\MultiTenancyBundle\Services\DbConfigService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceWiringTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private  $container;

    public static function tearDownAfterClass(): void
    {
        // this is important because this test generates a different Symfony
        // kernel for each configuration to avoid cache issues
        self::deleteDirectory(__DIR__.'/var/cache/test');
    }

    public function testDbConfigServiceWiring(): void
    {
        /** @var DbConfigService $dbConfigService */
        $dbConfigService = $this->container->get('test.service_container')->get('hakam_db_config.service');

        self::assertInstanceOf(DbConfigService::class, $dbConfigService);
    }

    public function testTenantEntityManagerServiceWiring(): void
    {
        /** @var TenantEntityManager $tenantEntityManagerService */
        $tenantEntityManagerService = $this->container->get('tenant_entity_manager');

        self::assertInstanceOf(TenantEntityManager::class, $tenantEntityManagerService);
    }

    protected function setUp(): void
    {
        $config = [
            'tenant_database_className' => '\Hakam\MultiTenancyBundle\Tests\FunctionalTest\src\\Entity\Main\TenantDbConfig',
            'tenant_database_identifier' => 'id',
            'tenant_connection' => [
                'host' => '127.0.0.1',
                'driver' => 'pdo_mysql',
                'charset' => 'utf8',
                'dbname' => 'tenant0',
                'user' => 'root',
                'password' => null
            ],
            'tenant_migration' =>
                [
                    'tenant_migration_namespace' => 'Application\Migrations\Tenant',
                    'tenant_migration_path' => 'migrations/Tenant'
                ],
            'tenant_entity_manager' =>
                [
                    'mapping' =>
                        [
                            'type' => 'annotation',
                            'dir' => '%kernel.project_dir%/src/Entity/Tenant',
                            'prefix' => 'Hakam\MultiTenancyBundle\Tests\FunctionalTest\src\Entity',
                            'alias' => 'Tenant'
                        ]
                ]
        ];
        $kernel = new MultiTenancyBundleTestingKernel($config);
        $kernel->boot();
        $this->container = $kernel->getContainer();
    }

    /**
     * Utility method because PHP doesn't allow to delete non-empty directories.
     */
    private static function deleteDirectory(string $dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ? \rmdir($fileinfo->getRealPath()) : \unlink($fileinfo->getRealPath());
        }

        \rmdir($dir);
    }
}
