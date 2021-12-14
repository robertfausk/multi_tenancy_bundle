<?php


namespace Hakam\MultiTenancyBundle\Command;


use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Ramy Hakam <pencilsoft1@gmail.com>
 */
trait CommandTrait
{
    protected function getDependencyFactory(InputInterface $input): DependencyFactory
    {
        if ($input->getArgument('dbId') !== null) {
            $switchEvent = new SwitchDbEvent($input->getArgument('dbId'));
            $this->eventDispatcher->dispatch($switchEvent);
        }
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManager('tenant');

        $tenantMigrationConfig = new ConfigurationArray(
            $this->container->getParameter('tenant_doctrine_migration')
        );

        return DependencyFactory::fromEntityManager($tenantMigrationConfig, new ExistingEntityManager($em));
    }

}
