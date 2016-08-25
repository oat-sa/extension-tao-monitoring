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

namespace oat\taoMonitoring\test\DeliveryLog;


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogService;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogStorageInterface;
use Prophecy\Argument;

class DeliveryLogServiceTest extends TaoPhpUnitTestRunner
{
    public function testService()
    {
        $storage = $this->prophesize(DeliveryLogStorageInterface::class);
        $storage->getRow(Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true);

        $storage->addExecution(Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true);

        $storage->addFinishedExecution(Argument::type('string'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true);


        $logService = new DeliveryLogService();
        $logService->setStorage($storage->reveal());

        $this->assertTrue($logService->getDeliveryLog('@1'));
        $this->assertTrue($logService->addExecution());
        $this->assertTrue($logService->addFinishedExecution());
    }
}
