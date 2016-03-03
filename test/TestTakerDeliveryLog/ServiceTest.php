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
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\test\TestTakerDeliveryLog\Mock\TestStorage;

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
        
        $this->storage = new TestStorage($this->service);
        $this->storage->createStorage();
        $this->service->setStorage($this->storage);
    }
    
    /**
     * @expectedException \common_Exception
     */
    public function testLogEventWithoutLogin()
    {
        $this->service->logEvent();
    }

    /**
     * @expectedException \common_Exception
     */
    public function testLogEventWithoutNbField()
    {
        $this->service->logEvent('tt1');
    }
    
    public function testLogEvent()
    {
        $login = 'tt1';
        $this->assertFalse($this->storage->getRow($login));
        
        $this->service->logEvent($login, StorageInterface::NB_ITEM);
        
        $this->assertEquals([StorageInterface::TEST_TAKER_LOGIN => $login,StorageInterface::NB_ITEM => 1,StorageInterface::NB_EXECUTIONS => 0,StorageInterface::NB_FINISHED => 0], $this->storage->getRow($login));

        $this->service->logEvent($login, StorageInterface::NB_ITEM);
        $this->service->logEvent($login, StorageInterface::NB_ITEM);
        $this->service->logEvent($login, StorageInterface::NB_ITEM);

        $this->service->logEvent($login, StorageInterface::NB_EXECUTIONS);
        $this->service->logEvent($login, StorageInterface::NB_EXECUTIONS);

        $this->service->logEvent($login, StorageInterface::NB_FINISHED);
        
        $this->assertEquals([StorageInterface::TEST_TAKER_LOGIN => $login,StorageInterface::NB_ITEM => 4,StorageInterface::NB_EXECUTIONS => 2,StorageInterface::NB_FINISHED => 1], $this->storage->getRow($login));
    }
    
    public function testUpdateTestTaker()
    {
        $login = 'tt1';
        $this->assertFalse($this->storage->getRow($login));
        
        $testData = [StorageInterface::TEST_TAKER_LOGIN => $login, StorageInterface::NB_ITEM => 4, StorageInterface::NB_EXECUTIONS => 34, StorageInterface::NB_FINISHED => 2];
        
        $aggregator = $this->prophesize('\oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface');
        $aggregator->getSlice()
            ->shouldBeCalledTimes(1)
            ->willReturn([$testData]);
        
        $this->service->updateTestTaker($login, $aggregator->reveal());
        $this->assertEquals($testData, $this->storage->getRow($login));
    }
}
