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
        $this->service = $this->prophesize('\oat\taoMonitoring\model\TestTakerDeliveryLogInterface');
        $this->service->setStorage(Argument::type('\oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->setStorage'])
            ->willReturn(true);
        
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
            'dataAggregator->getSlice' => 2
        ]);

        $login = '499 Tt';
        
        $tmpStorage = new TestStorage($this->service->reveal());
        $regularStorage = new TestStorage($this->service->reveal());

        $tmpStorage->createStorage();

        $this->assertFalse($tmpStorage->getRow($login));

        // push data in $tmpStorage for check move data to another storage
        $tmpStorage->createRow($login);
        $tmpStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $tmpStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $tmpStorage->incrementField($login, StorageInterface::NB_ITEM);

        $login2 = 'test';
        $tmpStorage->createRow($login2);
        $tmpStorage->incrementField($login2, StorageInterface::NB_EXECUTIONS);

        $this->assertEquals([
            'test_taker' => $login,
            'nb_item' => 1,
            'nb_executions' => 2,
            'nb_finished' => 0,
        ], $tmpStorage->getRow($login));

        //
        $regularStorage->createStorage();
        
        $this->updater = new Updater($this->service->reveal(), $tmpStorage, $regularStorage, $this->dataAggregator->reveal());
        $this->updater->execute();

        $row = $regularStorage->getRow($login);
        $this->assertEquals([
            StorageInterface::NB_ITEM => 25,
            StorageInterface::NB_EXECUTIONS => 10,
            StorageInterface::NB_FINISHED => 6, 
            StorageInterface::TEST_TAKER_LOGIN => $login], $row);

        $row = $regularStorage->getRow($login2);
        $this->assertEquals([
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 1,
            StorageInterface::NB_FINISHED => 0,
            StorageInterface::TEST_TAKER_LOGIN => $login2], $row);

    }
}
