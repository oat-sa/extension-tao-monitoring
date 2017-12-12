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


use oat\oatbox\service\ServiceNotFoundException;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\EventsHandler;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\TestTakerDeliveryActivityLogService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;
use oat\taoQtiTest\models\event\QtiMoveEvent;

/**
 * Class RegisterRdsTestTakerDeliveryActivityLog
 * @package oat\taoMonitoring\scripts\install
 */
class RegisterRdsTestTakerDeliveryActivityLog extends \common_ext_action_InstallAction
{
    
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';

        try {
            $service = $this->getServiceManager()->get(TestTakerDeliveryActivityLogInterface::SERVICE_ID);
        } catch (ServiceNotFoundException $exception) {
            $service = new TestTakerDeliveryActivityLogService(array(
                RdsStorage::OPTION_PERSISTENCE => $persistenceId,
            ));
            $service->setServiceManager($this->getServiceManager());
        }

        $persistence = $service->getOption(RdsStorage::OPTION_PERSISTENCE);


        /** @var RdsStorage $storage */
        $storage = new RdsStorage( $persistence );
        $storage->createStorage();

        //Service
        $this->registerService(TestTakerDeliveryActivityLogInterface::SERVICE_ID, $service);


        // Events

        // count executions
        $this->registerEvent(DeliveryExecutionCreated::class, [EventsHandler::class, 'deliveryExecutionCreated']);
        // finished executions
        $this->registerEvent(DeliveryExecutionState::class, [EventsHandler::class, 'deliveryExecutionState']);
        // catch switch items
        $this->registerEvent(QtiMoveEvent::class, [EventsHandler::class, 'qtiMoveEvent']);


        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery activity log for test taker'));
    }
}
