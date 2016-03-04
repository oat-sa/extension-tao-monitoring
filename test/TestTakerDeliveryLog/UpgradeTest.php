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
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade\Updater;
use oat\taoMonitoring\test\TestTakerDeliveryLog\Mock\TestStorage;
use Prophecy\Argument;

class UpgradeTest extends TaoPhpUnitTestRunner
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @var \oat\taoMonitoring\model\TestTakerDeliveryLogInterface'
     */
    private $service;
    
    private $dataAggregator;
    
    private function prepare(array $shouldBeCalledTimes)
    {
        $fiveHdrs = [];
        for ($i=0; $i<500; $i++) {
            $fiveHdrs[] = [
                StorageInterface::NB_ITEM => 12,
                StorageInterface::NB_EXECUTIONS => 4,
                StorageInterface::NB_FINISHED => 3,
                StorageInterface::TEST_TAKER_LOGIN => $i .' Tt'
            ];
        }
        
        /** @var \oat\taoMonitoring\model\TestTakerDeliveryLog\Service $service */
        $this->service = $this->prophesize('\oat\taoMonitoring\model\TestTakerDeliveryLog\Service');
        $this->service->setStorage(Argument::type('\oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->setStorage'])
            ->willReturn(true);
        $this->service->hasOption(Argument::type('string'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->hasOption'])
            ->willReturn(false);
        
        /** @var \oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface $dataAggregator */
        $this->dataAggregator = $this->prophesize('\oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface');
        $this->dataAggregator->countAllData()
            ->shouldBeCalledTimes($shouldBeCalledTimes['dataAggregator->countAllDeliveries'])
            ->willReturn(600);
        $this->dataAggregator->getSlice(Argument::type('integer'), Argument::type('integer'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['dataAggregator->getSlice'])
            ->willReturn($fiveHdrs);
    }

    public function testUpdate()
    {
        $this->prepare([
            'service->setStorage' => 2,
            'dataAggregator->countAllDeliveries' => 1,
            'dataAggregator->getSlice' => 2,
            'service->hasOption' => 1,
        ]);

        $login = '499 Tt';

        $regularStorage = new TestStorage($this->service->reveal());
        $regularStorage->createStorage();
        
        $tmpStorage = new TestStorage($this->service->reveal());
        $tmpStorage->createStorage();
        $this->assertFalse($tmpStorage->getRow($login));

        
        $this->updater = new Updater($this->service->reveal(), $tmpStorage, $regularStorage, $this->dataAggregator->reveal());
        $this->updater->execute();

        $row = $regularStorage->getRow($login);
        $this->assertEquals([
            StorageInterface::NB_ITEM => 24,
            StorageInterface::NB_EXECUTIONS => 8,
            StorageInterface::NB_FINISHED => 6, 
            StorageInterface::TEST_TAKER_LOGIN => $login], $row);
    }
}
