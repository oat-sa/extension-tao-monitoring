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

namespace oat\taoMonitoring\test\TestTakerDeliveryActivityLog;


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogStorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\StorageInterface;
use Prophecy\Argument;


class ServiceTest extends TaoPhpUnitTestRunner
{
    private $service;
    
    public function testService()
    {
        $storage = $this->prophesize(StorageInterface::class);
        $storage->event(Argument::type('string'), Argument::type('string'), Argument::type('string'), Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true);

        $arr = [['hour' => '2016-06-21 13:30:35', 'count' => 5], ['hour' => '2016-06-21 11:50:35', 'count' => 2]];
        $storage->getLastActivity(Argument::type('string'), Argument::type('string'), Argument::type('bool'))
            ->shouldBeCalledTimes(1)
            ->willReturn($arr);


        $this->service = new Service(['persistence' => 'default']);
        $this->service->setStorage($storage->reveal());

        $this->assertNull($this->service->event('tt1', 'delivery1', 'deliveryExecutionUri', 'testEvent'));
        $this->assertEquals($this->service->getLastActivity('delivery1', '-1 day', true), $arr);
    }

    public function testGetCountDeliveryExecutions()
    {
        $deliveryLog = $this->prophesize(DeliveryLogService::class);
        $arr = [
            DeliveryLogStorageInterface::DELIVERY => '#delivery',
            DeliveryLogStorageInterface::NB_EXECUTIONS => 1,
            DeliveryLogStorageInterface::NB_FINISHED => 0];
        $deliveryLog->getDeliveryLog(Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn($arr);

        $this->service = new Service(['persistence' => 'default']);
        $this->service->setDeliveryLogService($deliveryLog->reveal());

        $this->assertEquals($arr[DeliveryLogStorageInterface::NB_EXECUTIONS], $this->service->countDeliveryExecutions('#delivery'));
    }
}
