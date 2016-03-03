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
use oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;


/**
 * 
 * Interface TestTakerLogInterface
 * @package oat\taoMonitoring\model\Delivery
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */
interface TestTakerDeliveryLogInterface
{
    const SERVICE_ID = 'taoMonitoring/testTakerDeliveryLog';
    
    /**
     * Increment test taker event
     * (create row if not exists)
     *
     * @param string $testTakerLogin
     * @param string $nb_event
     * @return bool
     */
    public function logEvent($testTakerLogin = '', $nb_event = '');

    /**
     * Update all statistics for test taker
     * [We have aggregated statistics only (count of  the items), so I don't know how many times test taker complete one item]
     *
     * @param string $login
     * @param DataAggregatorInterface $aggregator
     * @return mixed
     */
    public function updateTestTaker($login = '', DataAggregatorInterface $aggregator);
    
    /**
     * Set storage for service data
     * 
     * @param StorageInterface $storage
     * @return mixed
     */
    public function setStorage(StorageInterface $storage);
}
