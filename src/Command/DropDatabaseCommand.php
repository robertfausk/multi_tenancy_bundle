<?phpdeclare(strict_types=1);namespace Hakam\MultiTenancyBundle\Command;use Doctrine\Persistence\ManagerRegistry;use Exception;use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;use Hakam\MultiTenancyBundle\Exception\MultiTenancyException;use Hakam\MultiTenancyBundle\Services\DbService;use Hakam\MultiTenancyBundle\Services\TenantDbConfigurationInterface;use Symfony\Component\Console\Attribute\AsCommand;use Symfony\Component\Console\Command\Command;use Symfony\Component\Console\Exception\ExceptionInterface;use Symfony\Component\Console\Input\InputArgument;use Symfony\Component\Console\Input\InputInterface;use Symfony\Component\Console\Input\InputOption;use Symfony\Component\Console\Output\OutputInterface;use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;#[AsCommand(    name: 'tenant:database:drop',    description: 'Proxy to drop an existing tenant database.',)]final class DropDatabaseCommand extends Command{    use CommandTrait;    public function __construct(        private ManagerRegistry $registry,        private DbService $dbService,        private EventDispatcherInterface $eventDispatcher    ) {        parent::__construct();    }    protected function configure(): void    {        $this            ->setDescription('Create and prepare  new databases for a tenant')            ->setAliases(['t:d:d'])            ->addArgument('dbId', InputArgument::REQUIRED, 'Tenant Db Identifier for database to be created.')            ->addOption('if-exists', null, InputOption::VALUE_NONE, 'Don\'t trigger an error, when the database doesn\'t exist')            ->setHelp('This command allows you to drop an existing database of a tenant which is removed from the main database config entity');    }    protected function execute(InputInterface $input, OutputInterface $output): int    {        try {            $dbConfig = $this->dbService->getDatabaseById($input->getArgument('dbId'));            $dbConfig->setDatabaseStatus(DatabaseStatusEnum::DATABASE_NOT_CREATED);            $this->dbService->dropDatabase($dbConfig->getDbName());            $this->registry->getManager()->persist($dbConfig);            $this->registry->getManager()->flush();            $output->writeln('Removed database successfully');            return 0;        } catch (MultiTenancyException $e) {            if ($input->getOption('if-exists')) {                return 1;            } else {                $output->writeln($e->getMessage());                return 1;            }        } catch (Exception $e) {            $output->writeln($e->getMessage());            return 1;        } catch (ExceptionInterface $e) {            $output->writeln($e->getMessage());            return 1;        }    }    /**     * @throws MultiTenancyException|\Doctrine\DBAL\Exception If the database does not exist or cannot be dropped.     */    private function dropDatabase(TenantDbConfigurationInterface $dbConfiguration): void    {        ;    }}