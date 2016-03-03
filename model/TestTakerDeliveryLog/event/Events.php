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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog\event;


use oat\oatbox\service\ServiceManager;
use oat\tao\helpers\UserHelper;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoMonitoring\model\TestTakerDeliveryLog\EventInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;
use oat\taoQtiTest\models\event\QtiMoveEvent;

class Events implements EventInterface
{
    /**
     * @var TestTakerDeliveryLogInterface
     */
    private static $service;
    
    public static function setService(TestTakerDeliveryLogInterface $service)
    {
        self::$service = $service;
    }
    
    /**
     * @return TestTakerDeliveryLogInterface
     */
    private static function service()
    {
        if (!isset(self::$service)) {
            self::setService( ServiceManager::getServiceManager()->get(TestTakerDeliveryLogInterface::SERVICE_ID) ); 
        }
        return self::$service;
    }
    
    public static function deliveryExecutionCreated(DeliveryExecutionCreated $event)
    {
        $login = self::userLoginFromDeliveryExecution( $event->getDeliveryExecution() );
        self::service()->logEvent($login, StorageInterface::NB_EXECUTIONS);
    }

    public static function deliveryExecutionState(DeliveryExecutionState $event)
    {
        if ($event->getState() === DeliveryExecution::STATE_FINISHIED) {
            $login = self::userLoginFromDeliveryExecution( $event->getDeliveryExecution() );
            self::service()->logEvent($login, StorageInterface::NB_FINISHED);
        }
    }
    
    public static function qtiMoveEvent(QtiMoveEvent $event)
    {
        // todo find login
        // reload all statistic for test taker
        if ($event->getContext() === QtiMoveEvent::CONTEXT_AFTER) {
            $event->getSession();
            self::service()->updateTestTaker('login');
        }
    }

    /**
     * @param DeliveryExecution $deliveryExecution
     * @return string
     */
    private static function userLoginFromDeliveryExecution(DeliveryExecution $deliveryExecution)
    {
        $userId = $deliveryExecution->getUserIdentifier();
        $user = UserHelper::getUser($userId);
        $login = UserHelper::getUserLogin($user);
        return $login;
    }
}
