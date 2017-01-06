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

namespace oat\taoMonitoring\scripts\update;


use \common_ext_ExtensionUpdater;
use oat\oatbox\event\EventManager;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\TestTakerDeliveryActivityLogService;
use oat\taoMonitoring\scripts\install\RegisterRdsDeliveryLog;
use oat\taoMonitoring\scripts\install\RegisterRdsTestTakerDeliveryActivityLog;
use oat\taoMonitoring\scripts\update\v0_1_0\DropTestTakerDeliveryLogTable;

class Updater extends common_ext_ExtensionUpdater {

    /**
     * @param string $initialVersion
     * @return string string
     */
    public function update($initialVersion)
    {
        
        if ($this->isVersion('0.0.1')) {

            if (!$this->getServiceManager()->has(TestTakerDeliveryActivityLogService::SERVICE_ID)) {
                $action = new RegisterRdsTestTakerDeliveryActivityLog();
                $action->setServiceLocator($this->getServiceManager());
                $action->__invoke(array('default'));
            }

            if (!$this->getServiceManager()->has(DeliveryLogService::SERVICE_ID)) {
                $action = new RegisterRdsDeliveryLog();
                $action->setServiceLocator($this->getServiceManager());
                $action->__invoke(array('default'));
            }

            $this->setVersion('0.0.2');
        }

        if ($this->isVersion('0.0.2')) {

            $eventManager = $this->getServiceManager()->get(EventManager::CONFIG_ID);

            // Detach switch items - on switching recount all statistic for testTaker
            $eventManager->detach(
                'oat\\taoQtiTest\\models\\event\\QtiMoveEvent',
                array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\event\\Events', 'qtiMoveEvent')
            );

            // count executions
            $eventManager->detach(
                'oat\\taoDelivery\\models\\classes\\execution\\event\\DeliveryExecutionCreated',
                array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\event\\Events', 'deliveryExecutionCreated')
            );

            // finished executions
            $eventManager->detach(
                'oat\\taoDelivery\\models\\classes\\execution\\event\\DeliveryExecutionState',
                array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\event\\Events', 'deliveryExecutionState')
            );

            $this->getServiceManager()->register(EventManager::CONFIG_ID, $eventManager);


            $this->getServiceManager()->unregister('taoMonitoring/testTakerDeliveryLog');

            // delete from storage
            $action = new DropTestTakerDeliveryLogTable();
            $action([]);

            $this->setVersion('0.1.0');
        }

        $this->skip('0.1.0', '0.3.0');
    }
}
