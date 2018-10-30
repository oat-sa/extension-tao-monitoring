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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoMonitoring\scripts\install;


use oat\oatbox\event\EventManager;
use oat\oatbox\extension\InstallAction;
use oat\tao\model\actionQueue\event\InstantActionOnQueueEvent;
use oat\taoMonitoring\model\InstantActionQueueLog\event\InstantActionQueueLogEvent;
use oat\taoMonitoring\model\InstantActionQueueLog\InstantActionQueueLogService;
use oat\taoMonitoring\model\InstantActionQueueLog\storage\InstantActionQueueLogRdsStorage;

class RegisterInstantActionQueueLog extends InstallAction
{
    /**
     * @param $params
     * @return \common_report_Report
     * @throws \common_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';

        // Register new service
        $this->getServiceManager()->register(InstantActionQueueLogService::SERVICE_ID, new InstantActionQueueLogService([InstantActionQueueLogRdsStorage::OPTION_PERSISTENCE => $persistenceId]));

        // creating storage
        $storage = new InstantActionQueueLogRdsStorage( $persistenceId );
        $storage->createStorage();

        $eventManager = $this->getServiceManager()->get(EventManager::SERVICE_ID);
        $eventManager->attach(InstantActionOnQueueEvent::class, array(InstantActionQueueLogEvent::class, 'queued'));
        $this->getServiceManager()->register(EventManager::SERVICE_ID, $eventManager);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered InstantActionQueue monitoring'));
    }
}
