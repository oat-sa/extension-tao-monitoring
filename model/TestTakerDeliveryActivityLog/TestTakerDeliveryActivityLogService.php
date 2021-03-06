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


use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogStorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;


class TestTakerDeliveryActivityLogService extends ConfigurableService
    implements TestTakerDeliveryActivityLogInterface
{

    /**
     * @var DeliveryLogService
     */
    private $deliveryLogService;

    /**
     * @var StorageInterface
     */
    private $storage;

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }
    
    public function event($testTaker='', $delivery='', $deliveryExecution='', $event='')
    {
        $this->storage()->event($testTaker, $delivery, $deliveryExecution, $event);
    }

    private function storage()
    {
        if (!isset($this->storage)) {
            $this->storage = new RdsStorage($this->getOption(RdsStorage::OPTION_PERSISTENCE));
        }

        return $this->storage;
    }
    
    public function getLastActivity($deliveryUri = '', $dateRange = '-1 day', $onlyActive = false)
    {
        return $this->storage()->getLastActivity($deliveryUri, $dateRange, $onlyActive);
    }

    /**
     * @return DeliveryLogService
     */
    public function getDeliveryLogService()
    {
        if (!$this->deliveryLogService) {
            $this->deliveryLogService = ServiceManager::getServiceManager()->get(DeliveryLogService::SERVICE_ID);
        }

        return $this->deliveryLogService;
    }

    /**
     * @param DeliveryLogService $deliveryLogService
     */
    public function setDeliveryLogService(DeliveryLogService $deliveryLogService)
    {
        $this->deliveryLogService = $deliveryLogService;
    }

    public function countDeliveryExecutions($deliveryUri = '')
    {
        $count = 0;
        $log = $this->getDeliveryLogService()->getDeliveryLog($deliveryUri);
        if ($log) {
            $count = $log[DeliveryLogStorageInterface::NB_EXECUTIONS];
        }

        return $count;
    }
}
