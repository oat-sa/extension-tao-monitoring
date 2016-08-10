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

namespace oat\taoMonitoring\model\DeliveryLog;


use oat\oatbox\service\ConfigurableService;
use oat\taoMonitoring\model\DeliveryLog\storage\DeliveryLogRdsStorage;

/**
 * Aggregated data for the delivery
 *
 * Class DeliveryLogService
 * @package oat\taoMonitoring\model
 */
class DeliveryLogService extends ConfigurableService
{
    const SERVICE_ID = 'taoMonitoring/deliveryLog';

    /**
     * @var DeliveryLogStorageInterface
     */
    private $storage;

    public function setStorage(DeliveryLogStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    private function storage()
    {
        if (!isset($this->storage)) {
            $this->storage = new DeliveryLogRdsStorage($this->getOption(DeliveryLogRdsStorage::OPTION_PERSISTENCE));
        }

        return $this->storage;
    }

    /**
     * @param string $deliveryUri
     * @return array|bool
     */
    public function getDeliveryLog($deliveryUri = '')
    {
        return $this->storage()->getRow($deliveryUri);
    }

    /**
     * new Execution created
     * @param string $deliveryUri
     * @return bool
     */
    public function addExecution($deliveryUri = '')
    {
        return $this->storage()->addExecution($deliveryUri);
    }

    /**
     * Execution was finished
     * @param string $deliveryUri
     * @return bool
     */
    public function addFinishedExecution($deliveryUri = '')
    {
        return $this->storage()->addFinishedExecution($deliveryUri);
    }

}
