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

namespace oat\taoMonitoring\scripts\install;


use oat\oatbox\event\EventManager;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\DeliveryLog\event\DeliveryLogEvent;
use oat\taoMonitoring\model\DeliveryLog\storage\DeliveryLogRdsStorage;

/**
 * Class RegisterRdsTestTakerDeliveryLog
 * @package oat\taoMonitoring\scripts\install
 */
class RegisterRdsDeliveryLog extends \common_ext_action_InstallAction
{
    
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';

        /** Register new service */
        $this->getServiceManager()->register(DeliveryLogService::SERVICE_ID, new DeliveryLogService([DeliveryLogRdsStorage::OPTION_PERSISTENCE => $persistenceId]));

        /** @var DeliveryLogRdsStorage $storage */
        $storage = new DeliveryLogRdsStorage( $persistenceId );
        $storage->createStorage();
        
        $this->appendEvents();
        
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery log for test taker'));
    }
    
    private function appendEvents()
    {
        $eventManager = $this->getServiceManager()->get(EventManager::CONFIG_ID);

        // count executions
        $eventManager->attach(
            DeliveryExecutionCreated::class,
            array(DeliveryLogEvent::class, 'deliveryExecutionCreated')
        );

        // finished executions
        $eventManager->attach(
            DeliveryExecutionState::class,
            array(DeliveryLogEvent::class, 'deliveryExecutionState')
        );

        $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);
    }
}
