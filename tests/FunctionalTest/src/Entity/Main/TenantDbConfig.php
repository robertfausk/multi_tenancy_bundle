<?php
declare(strict_types=1);

namespace Hakam\MultiTenancyBundle\Tests\FunctionalTest\src\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Hakam\MultiTenancyBundle\Services\TenantDbConfigurationInterface;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class TenantDbConfig implements TenantDbConfigurationInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="int", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getDbName()
    {
        return 'tenant';
    }

    public function getDbUsername()
    {
        return 'root';
    }

    public function getDbPassword()
    {
        return '12345678';
    }
}
