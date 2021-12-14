<?php


namespace Hakam\MultiTenancyBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Ramy Hakam <pencilsoft1@gmail.com>
 */
class SwitchDbEvent extends Event
{
    /**
     * @var string
     */
    private $dbIndex;

    /**
     * @var bool
     */
    private $isWithReconnect;

    public function __construct( string $tenantDbIndex, bool $isWithReconnect = true)
    {
        $this->dbIndex = $tenantDbIndex;
        $this->isWithReconnect = $isWithReconnect;
    }

    public function getDbIndex(): string
    {
        return $this->dbIndex;
    }

    public function isWithReconnect(): bool
    {
        return $this->isWithReconnect;
    }
}
