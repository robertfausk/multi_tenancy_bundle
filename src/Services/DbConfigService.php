<?php


namespace Hakam\MultiTenancyBundle\Services;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use RuntimeException;

/**
 * @author Ramy Hakam <pencilsoft1@gmail.com>
 */
class DbConfigService
{
    /**
     * @var ServiceEntityRepositoryInterface
     */
    private $entityRepository;
    /**
     * @var string
     */
    private $dbIdentifier;

    public function __construct(EntityManagerInterface $entityManager, string $dbClassName, string $dbIdentifier)
    {
        $this->dbIdentifier = $dbIdentifier;
        $this->entityRepository = $entityManager->getRepository($dbClassName);
    }

    public function findDbConfig(string $identifier): TenantDbConfigurationInterface
    {
        $dbConfigObject = $this->entityRepository->findOneBy([$this->dbIdentifier => $identifier]);

        if ($dbConfigObject === null) {
            throw new RuntimeException(
                \sprintf(
                    'Tenant db repository " %s " returns NULL for identifier " %s = %s " ',
                    \get_class($this->entityRepository),
                    $this->dbIdentifier,
                    $identifier
                )
            );
        }

        if (!$dbConfigObject instanceof TenantDbConfigurationInterface) {
            throw new LogicException(
                \sprintf(
                    'The tenant db entity  " %s ". Should implement " %s " ',
                    \get_class($dbConfigObject),
                    TenantDbConfigurationInterface::class
                )
            );
        }

        return $dbConfigObject;
    }

}
