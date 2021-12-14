<?php
declare(strict_types=1);

namespace Hakam\MultiTenancyBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class DropDatabaseCommand extends Command
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(Registry $registry, ContainerInterface $container, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->registry = $registry;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this
            ->setName('tenant:database:drop')
            ->setDescription('Proxy to launch doctrine:database:create with custom database .')
            ->addArgument('dbId', InputArgument::REQUIRED, 'Tenant Db Identifier for database to be created.')
            ->addOption('if-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the database doesn\'t exist')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Set this parameter to execute this action');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newInput = new ArrayInput([
            '--connection' => 'tenant',
            '--if-exists' => $input->getOption('if-exists'),
            '--force' => $input->getOption('force'),
        ]);
        $newInput->setInteractive($input->isInteractive());
        $otherCommand = new DropDatabaseDoctrineCommand($this->registry);
        $this->getDependencyFactory($input);
        $otherCommand->setApplication(new Application($this->container->get('kernel')));
        $otherCommand->run($newInput, $output);

        return 0;
    }

    protected function getDependencyFactory(InputInterface $input): DependencyFactory
    {
        if ($input->getArgument('dbId') !== null) {
            $switchEvent = new SwitchDbEvent($input->getArgument('dbId'), false);
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
