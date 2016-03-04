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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade;


use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\TestTakerDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\UpgradeInterface;

/**
 * 1. change storage to tmp
 * 2. truncate rds storage data
 * 3. generate new data from src
 * 4. change storage to rds
 * 5. move data from tmp to rds
 * 6. drop tmp
 * Class Updater
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog
 */
class Updater implements UpgradeInterface
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var StorageInterface
     */
    private $tmpStorage;

    /**
     * @var StorageInterface
     */
    private $regularStorage;

    /**
     * @var DataAggregatorInterface
     */
    private $dataAggregator;

    /**
     * Updater constructor.
     * @param Service $service
     * @param StorageInterface $tmpStorage
     * @param StorageInterface $regularStorage
     * @param DataAggregatorInterface $dataAggregator
     */
    public function __construct(
        Service $service,
        StorageInterface $tmpStorage,
        StorageInterface $regularStorage,
        DataAggregatorInterface $dataAggregator
    )
    {
        $this->service = $service;
        $this->tmpStorage = $tmpStorage;
        $this->regularStorage = $regularStorage;
        $this->dataAggregator = $dataAggregator;
    }

    public function execute()
    {
        if ($this->service->hasOption('tmpPath')) {
            throw new \common_Exception('Updating for test takers delivery log is running, wait for complete');
        }

        $this->service->setStorage($this->tmpStorage);
        $this->regularStorage->dropStorage();
        $this->regularStorage->createStorage();
        $this->generateStatistic($this->regularStorage);
        $this->service->setStorage($this->regularStorage);
        $this->updateAffectedData();
        $this->tmpStorage->dropStorage();
    }

    private function generateStatistic(StorageInterface $storage)
    {
        $total = $this->dataAggregator->countAllData();
        $inPage = 500;

        for ($page = 0; $page * $inPage < $total; $page++) {
            $statistics = $this->dataAggregator->getSlice($page, $inPage);
            if (count($statistics)) {
                $storage->flushArray($statistics);
            }
        }
    }

    private function updateAffectedData()
    {
        if ($this->tmpStorage->countAllData()) {
            $data = [];
            foreach($this->tmpStorage->getSlice() as $row) {
                $data[] = $row[StorageInterface::TEST_TAKER_LOGIN];
            }
            $data = array_unique($data);
            
            foreach ($data as $login) {
                $aggregator = TestTakerDataAggregator::factory( $login );
                if ($aggregator) {
                    $this->service->updateTestTaker($aggregator);
                }
            }
        }
    }
    
}
