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
use oat\tao\model\actionQueue\event\InstantActionOnQueueEvent;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\InstantActionQueueLog\event\InstantActionQueueLogEvent;
use oat\taoMonitoring\model\InstantActionQueueLog\InstantActionQueueLogService;
use oat\taoMonitoring\model\InstantActionQueueLog\storage\InstantActionQueueLogRdsStorage;
use oat\taoMonitoring\model\MonitoringPlugService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\TestTakerDeliveryActivityLogService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;
use oat\taoMonitoring\scripts\install\RegisterRdsDeliveryLog;
use oat\taoMonitoring\scripts\install\RegisterRdsTestTakerDeliveryActivityLog;

class Updater extends common_ext_ExtensionUpdater {

    /**
     * @param $initialVersion
     * @return string|void
     * @throws \common_Exception
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

            /** @var EventManager $eventManager */
            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);

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

            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);


            $this->getServiceManager()->unregister('taoMonitoring/testTakerDeliveryLog');

            $this->addReport(\common_report_Report::createInfo('Delete the table `monitoring_testtaker_deliveries` directly to clean the storage'));
            $this->setVersion('0.1.0');
        }

        $this->skip('0.1.0', '2.0.1');

        if ($this->isVersion('2.0.1')) {

            $this->getServiceManager()->register(MonitoringPlugService::SERVICE_ID, new MonitoringPlugService([
                'services' => [
                    // restore previous active services to not break behaviour
                    DeliveryLogService::SERVICE_ID,
                    TestTakerDeliveryActivityLogInterface::SERVICE_ID,
                ]
            ]));

            $this->addReport(\common_report_Report::createInfo('Check that configuration for the MonitoringPlugService is correct and content the services that needed by the environment'));

            $this->setVersion('2.1.0');
        }

        if ($this->isVersion('2.1.0')) {

            $this->getServiceManager()->register(InstantActionQueueLogService::SERVICE_ID, new InstantActionQueueLogService([InstantActionQueueLogRdsStorage::OPTION_PERSISTENCE => 'default']));

            $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
            $eventManager->attach(InstantActionOnQueueEvent::class, array(InstantActionQueueLogEvent::class, 'queued'));
            $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

            $this->logNotice('Run scripts/tools/CreateInstantActionQueueRds.php to create new storage');

            $this->setVersion('2.2.0');
        }

        if ($this->isVersion('2.2.0')) {
            // installator didn't work in the previous version of the updater
            if (!$this->getServiceManager()->has(InstantActionQueueLogService::SERVICE_ID) ) {
                $this->getServiceManager()->register(InstantActionQueueLogService::SERVICE_ID,
                    new InstantActionQueueLogService([InstantActionQueueLogRdsStorage::OPTION_PERSISTENCE => 'default']));

                $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
                $eventManager->attach(InstantActionOnQueueEvent::class,
                    array(InstantActionQueueLogEvent::class, 'queued'));
                $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

                $storage = new InstantActionQueueLogRdsStorage( 'default' );
                $storage->createStorage();
            }
            $this->setVersion('2.2.1');
        }

        if ($this->isVersion('2.2.1')) {
            // fixed previous broken updater when storage should be created by hand
            $storage = new InstantActionQueueLogRdsStorage( 'default' );
            $storage->createStorage();
            $this->setVersion('2.2.2');
        }

        $this->skip('2.2.2', '3.1.1');
    }
}
