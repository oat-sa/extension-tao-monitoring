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

namespace oat\taoMonitoring\test\TestTakerDeliveryLog;


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\LocalStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

/**
 * Class ServiceTest
 * @package oat\taoMonitoring\test\TestTakerDeliveryLog
 */
class ServiceTest extends TaoPhpUnitTestRunner
{
    /**
     * @var Service
     */
    private $service;

    private $storage;
    
    public function setUp()
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();

        $this->service = new Service(['persistence' => 'default']);
        
        $this->storage = new LocalStorage($this->service);
        $this->storage->createStorage();
        $this->service->setStorage($this->storage);
    }
    
    public function testUpdateTestTaker()
    {
        $login = 'tt1';
        $this->assertFalse($this->storage->getRow($login));
        
        $testData = [StorageInterface::TEST_TAKER_LOGIN => $login, StorageInterface::NB_EXECUTIONS => 34, StorageInterface::NB_FINISHED => 2];
        
        $aggregator = $this->prophesize('\oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface');
        $aggregator->getSlice()
            ->shouldBeCalledTimes(1)
            ->willReturn([$testData]);
        
        $this->service->updateTestTaker($aggregator->reveal());
        $this->assertEquals($testData, $this->storage->getRow($login));
    }
}
