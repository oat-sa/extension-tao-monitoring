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

namespace oat\taoMonitoring\model\InstantActionQueueLog;


use oat\oatbox\service\ConfigurableService;
use oat\tao\model\actionQueue\event\InstantActionOnQueueEvent;
use oat\taoMonitoring\model\InstantActionQueueLog\storage\InstantActionQueueLogRdsStorage;

class InstantActionQueueLogService extends ConfigurableService
{
    const SERVICE_ID = 'taoMonitoring/LoginQueueLogService';

    /**
     * @var InstantActionQueueLogStorageInterface
     */
    private $storage;

    public function setStorage(InstantActionQueueLogStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    private function storage()
    {
        return $this->storage ?: new InstantActionQueueLogRdsStorage($this->getOption(InstantActionQueueLogRdsStorage::OPTION_PERSISTENCE));
    }

    public function saveEvent(InstantActionOnQueueEvent $event)
    {
        $resourceId = '';
        if (method_exists($event->getQueuedAction(), 'getDelivery')) {
            $resourceId = $event->getQueuedAction()->getDelivery()->getUri();
        }

        $queueKey = $event->getInstantQueueKey() . '_' . session_id();

        $this->storage()->saveAction([
            InstantActionQueueLogStorageInterface::PARAM_ACTION_TYPE => $event->getActionType(),
            InstantActionQueueLogStorageInterface::PARAM_ACTION_TIME => time(),
            InstantActionQueueLogStorageInterface::PARAM_USER_ID => $event->getUser()->getIdentifier(),
            InstantActionQueueLogStorageInterface::PARAM_QUEUE_KEY => $queueKey,
            InstantActionQueueLogStorageInterface::PARAM_RESOURCE_ID => $resourceId,
        ]);
    }
}
