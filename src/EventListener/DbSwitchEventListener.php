<?php


namespace Hakam\MultiTenancyBundle\EventListener;


use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;
use Hakam\MultiTenancyBundle\Services\DbConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Ramy Hakam <pencilsoft1@gmail.com>
 */
class DbSwitchEventListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DbConfigService
     */
    private $dbConfigService;

    public function __construct(ContainerInterface $container,DbConfigService $dbConfigService)
    {
        $this->container = $container;
        $this->dbConfigService = $dbConfigService;
    }

    public static function getSubscribedEvents(): array
    {
      return
      [
          SwitchDbEvent::class => 'onHakamMultiTenancyBundleEventSwitchDbEvent'
      ];
    }

    public function onHakamMultiTenancyBundleEventSwitchDbEvent(SwitchDbEvent $switchDbEvent): void
    {
        $dbConfig = $this->dbConfigService->findDbConfig($switchDbEvent->getDbIndex());

        $tenantConnection = $this->container->get('doctrine')->getConnection('tenant');
        $tenantConnection->changeParams($dbConfig->getDbName(), $dbConfig->getDbUsername(), $dbConfig->getDbPassword());
        $tenantConnection->reconnect();
        if ($switchDbEvent->isWithReconnect()) {
            $tenantConnection->reconnect();
        }
    }
}
