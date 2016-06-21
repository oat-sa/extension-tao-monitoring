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

namespace oat\taoMonitoring\model;


use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\StorageInterface;


/**
 * Logging all test taker activity in delivery
 * and store this data about one last week
 *
 * Usages:
 *      for determine connected to delivery users
 *      for build 24 hours bar char
 *
 *
 * Interface TestTakerLogInterface
 * @package oat\taoMonitoring\model\Delivery
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */
interface TestTakerDeliveryActivityLogInterface
{
    const SERVICE_ID = 'taoMonitoring/testTakerDeliveryActivityLog';

    /**
     * Set storage for service data
     *
     * @param StorageInterface $storage
     * @return mixed
     */
    public function setStorage(StorageInterface $storage);

    /**
     * Load user activity in deliveries in last 24 hours, splitted by hours
     * @param string $deliveryUri
     * @param string $dateRange
     * @param bool $onlyActive
     * @return array
     */
    public function getLastActivity($deliveryUri = '', $dateRange = '-1 day', $onlyActive = false);

    /**
     * Write event to storage 
     * 
     * @param string $testTaker
     * @param string $delivery
     * @param string $deliveryExecution
     * @param string $event
     * @return mixed
     */
    public function event($testTaker='', $delivery='', $deliveryExecution='', $event='');
}
