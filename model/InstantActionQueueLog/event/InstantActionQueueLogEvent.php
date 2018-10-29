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

namespace oat\taoMonitoring\model\LoginQueueLog\event;


use oat\oatbox\service\ServiceManager;
use oat\tao\model\actionQueue\event\InstantActionOnQueueEvent;
use oat\taoMonitoring\model\LoginQueueLog\InstantActionQueueLogService;
use oat\taoMonitoring\model\MonitoringPlugService;

class InstantActionQueueLogEvent
{
    /**
     * Getting state of the service (it should be active to be able to work)
     * @return boolean
     */
    public static function isServiceActive()
    {
        return self::getServiceManager()
            ->get(MonitoringPlugService::SERVICE_ID)
            ->isServiceActive(InstantActionQueueLogService::SERVICE_ID);
    }

    public static function queued(InstantActionOnQueueEvent $event)
    {
        $service = self::getServiceManager()->get(InstantActionQueueLogService::SERVICE_ID);
        $service->saveEvent($event);
    }

    public static function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }
}
