<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 * 
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoMonitoring\model\TestTakerDeliveryActivityLog;


use oat\oatbox\service\ServiceManager;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoMonitoring\model\MonitoringPlugService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;
use oat\taoQtiTest\models\event\QtiMoveEvent;
use oat\taoDelivery\model\execution\ServiceProxy;

class EventsHandler implements EventsHandlerInterface
{
    /**
     * @var TestTakerDeliveryActivityLogInterface
     */
    private static $service;

    /**
     * @var bool
     */
    private static $active;

    public static function setService(TestTakerDeliveryActivityLogInterface $service)
    {
        self::$service = $service;
    }
    
    /**
     * @return TestTakerDeliveryActivityLogInterface
     */
    private static function service()
    {
        if (!isset(self::$service)) {
            /** @var TestTakerDeliveryActivityLogInterface $ttDeliveryActivityLog */
            $ttDeliveryActivityLog = ServiceManager::getServiceManager()->get(TestTakerDeliveryActivityLogInterface::SERVICE_ID);
            self::setService( $ttDeliveryActivityLog );
        }
        return self::$service;
    }

    /**
     * Getting state of the service (it should be active to be able to work)
     * @return boolean
     */
    private static function isServiceActive()
    {
        if (!isset(self::$active)) {
            self::$active = self::getServiceManager()
                ->get(MonitoringPlugService::SERVICE_ID)
                ->isServiceActive(TestTakerDeliveryActivityLogInterface::SERVICE_ID);
        }
        return self::$active;
    }

    public static function deliveryExecutionCreated(DeliveryExecutionCreated $event)
    {
        if (!self::isServiceActive()) {
            return;
        }
        self::event($event->getDeliveryExecution(), $event->getName());
    }

    public static function deliveryExecutionState(DeliveryExecutionState $event)
    {
        if (!self::isServiceActive()) {
            return;
        }
        if ($event->getState() === DeliveryExecutionInterface::STATE_FINISHIED) {
            self::event($event->getDeliveryExecution(), $event->getName());
        }
    }
    
    public static function qtiMoveEvent(QtiMoveEvent $event)
    {
        if (!self::isServiceActive()) {
            return;
        }
        if ($event->getContext() === QtiMoveEvent::CONTEXT_BEFORE) {
            $executionService = ServiceProxy::singleton();
            $deliveryExecution = $executionService->getDeliveryExecution($event->getSession()->getSessionId());
            self::event($deliveryExecution, $event->getName());
        }
    }
    
    private static function event(DeliveryExecutionInterface $deliveryExecution, $event)
    {
        if (!self::isServiceActive()) {
            return;
        }
        try {
            $testTaker = $deliveryExecution->getUserIdentifier();
            $delivery = $deliveryExecution->getDelivery();

            self::service()->event($testTaker, $delivery->getUri(), $deliveryExecution->getIdentifier(), $event);
        } catch (\Exception $e) {
            // failure in event should not stop execution
            \common_Logger::e('Failed to processing data for log TestTakerDeliveryActivityLog "' . $e->getMessage() . '"');
        }
    }

    public static function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
