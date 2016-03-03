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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog;


use oat\oatbox\service\ConfigurableService;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Storage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class Service extends ConfigurableService
    implements TestTakerDeliveryLogInterface
{

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param string $testTakerLogin
     * @param string $nb_event
     * @throws \common_Exception
     * @return bool
     */
    public function logEvent($testTakerLogin = '', $nb_event = '')
    {

        if (!isset($testTakerLogin) || empty($testTakerLogin)) {
            throw new \common_Exception('TestTakerDeliveryLogService should have test taker login');
        }

        $testTakerLog = $this->storage()->getRow($testTakerLogin);

        if (!$testTakerLog || !count($testTakerLog)) {
            //create record
            $this->storage()->createRow($testTakerLogin);
            $testTakerLog = $this->storage()->getRow($testTakerLogin);
        }

        if (!isset($testTakerLog[$nb_event])) {
            throw new \common_Exception('TestTakerDeliveryLogService has incorrect $nb_event');
        }

        $this->storage()->incrementField($testTakerLogin, $nb_event);

        return true;
    }
    
    public function updateTestTaker($login = '', DataAggregatorInterface $aggregator)
    {
        // recount statistics for test taker
        $statistics = current($aggregator->getSlice());
        $this->storage()->replace($statistics);
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    private function storage()
    {
        if (!isset($this->storage)) {
            $this->storage = $this->isLocked() 
                ? new TmpStorage($this->getOption('tmpPath')) 
                : new RdsStorage($this->getOptions());
        }

        return $this->storage;
    }
}
