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

namespace oat\taoMonitoring\model\DeliveryLog\event;


use oat\oatbox\service\ServiceManager;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogEventInterface;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;

class DeliveryLogEvent implements DeliveryLogEventInterface
{
    /**
     * @var DeliveryLogService
     */
    private static $service;

    public static function setService(DeliveryLogService $service)
    {
        self::$service = $service;
    }

    /**
     * @return DeliveryLogService
     */
    private static function service()
    {
        if (!isset(self::$service)) {
            self::setService( ServiceManager::getServiceManager()->get(DeliveryLogService::SERVICE_ID) );
        }
        return self::$service;
    }

    public static function deliveryExecutionCreated(DeliveryExecutionCreated $event)
    {
        try {
            self::service()->addExecution($event->getDeliveryExecution()->getDelivery()->getUri());
        } catch (\Exception $e) {
            // failure in event should not stop execution
            \common_Logger::e('Failed to processing data for log DeliveryLog (deliveryExecutionCreated) "' . $e->getMessage() . '"');
        }
    }

    public static function deliveryExecutionState(DeliveryExecutionState $event)
    {
        try {
            if ($event->getState() === DeliveryExecution::STATE_FINISHIED) {
                self::service()->addFinishedExecution($event->getDeliveryExecution()->getDelivery()->getUri());
            }
        } catch (\Exception $e) {
            // failure in event should not stop execution
            \common_Logger::e('Failed to processing data for log DeliveryLog (deliveryExecutionState) "' . $e->getMessage() . '"');
        }
    }
}
