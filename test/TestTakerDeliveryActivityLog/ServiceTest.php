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
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\StorageInterface;
use Prophecy\Argument;


class ServiceTest extends TaoPhpUnitTestRunner
{
    private $service;
    
    public function testService()
    {
        $this->service = new Service(['persistence' => 'default']);

        $storage = $this->prophesize(StorageInterface::class);
        $storage->event(Argument::type('string'), Argument::type('string'), Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true);
        $storage->getLastActivity(Argument::type('string'), Argument::type('string'), Argument::type('bool'))
            ->shouldBeCalledTimes(1)
            ->willReturn([['hour' => '2016-06-21 13:30:35', 'count' => 5], ['hour' => '2016-06-21 11:50:35', 'count' => 2]]);

        $this->service->setStorage($storage->reveal());

        $this->service->event('tt1', 'delivery1', 'testEvent');
        $this->service->getLastActivity('delivery1', '-1 day', true);
    }
}
