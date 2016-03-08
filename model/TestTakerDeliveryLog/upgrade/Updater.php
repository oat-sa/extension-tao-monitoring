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


use oat\oatbox\service\ServiceManager;
use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\TestTakerDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\UpgradeInterface;

/**
 * 1. change storage to tmp
 * 2. truncate rds storage data
 * 3. generate new data from src
 * 4. change storage to rds
 * 5. move data from tmp to rds
 * 6. drop tmp
 * 
 * Class Updater
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade
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
        if ($this->service->hasOption(TmpStorage::OPTION_TMP_FILE)) {
            throw new \common_Exception('Updating for test takers delivery log is running, wait for complete');
        }

        $this->switchToTmpStorage();
        
        $this->regularStorage->dropStorage();
        $this->regularStorage->createStorage();
        $this->generateStatistic($this->regularStorage);
        
        $this->switchToRegularStorage();
        $this->updateAffectedData();
        $this->tmpStorage->dropStorage();
    }

    private function switchToTmpStorage()
    {
        $this->tmpStorage->dropStorage();
        
        $config = $this->service->getOptions();
        $config[TmpStorage::OPTION_TMP_FILE] = $this->tmpStorage->createStorage();
        
        $service = new Service($config);
        $serviceManager = ServiceManager::getServiceManager();
        $service->setServiceManager($serviceManager);

        $serviceManager->register(Service::SERVICE_ID, $service);
        
        $this->service->setOptions($config);
        $this->service->setStorage($this->tmpStorage);
    }
    
    private function switchToRegularStorage()
    {
        $config = $this->service->getOptions();
        if (isset($config[TmpStorage::OPTION_TMP_FILE])) {
            unset($config[TmpStorage::OPTION_TMP_FILE]);
        }

        $service = new Service($config);
        $serviceManager = ServiceManager::getServiceManager();
        $service->setServiceManager($serviceManager);

        $serviceManager->register(Service::SERVICE_ID, $service);

        $this->service->setOptions($config);
        $this->service->setStorage($this->regularStorage);
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

    protected function updateAffectedData()
    {
        $count = 0;
        if ($this->tmpStorage->countAllData()) {
            $data = [];
            foreach($this->tmpStorage->getSlice() as $row) {
                $data[] = $row[StorageInterface::TEST_TAKER_LOGIN];
            }
            $data = array_unique($data);
            
            foreach ($data as $login) {
                $aggregator = $this->getTestTakerAggregator($login);
                if ($aggregator) {
                    $this->service->updateTestTaker($aggregator);
                    $count++;
                }
            }
        }
        return $count;
    }
    
    protected function getTestTakerAggregator($login)
    {
        return TestTakerDataAggregator::factory( $login );
    }
    
}
