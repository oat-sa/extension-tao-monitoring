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
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\LocalStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade\Updater;
use oat\taoMonitoring\test\TestTakerDeliveryLog\Mock\StubUpdater;
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

    /** @var \oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface $dataAggregator */
    private $dataAggregator;

    private function prepare(array $shouldBeCalledTimes)
    {
        $fiveHdrs = [];
        for ($i = 0; $i < 500; $i++) {
            $fiveHdrs[] = [
                StorageInterface::NB_ITEM => 12,
                StorageInterface::NB_EXECUTIONS => 4,
                StorageInterface::NB_FINISHED => 3,
                StorageInterface::TEST_TAKER_LOGIN => $i . ' Tt'
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
        $this->service->updateTestTaker(Argument::type('\oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->updateTestTaker'])
            ->willReturn(true);
        $this->service->getOptions()
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->getOptions'])
            ->willReturn(['persistence' => 'default']);
        $this->service->setOptions(Argument::type('array'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['service->setOptions'])
            ->willReturn(['persistence' => 'default']);

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
            'service->hasOption' => 1,
            'service->getOptions' => 2,
            'service->setOptions' => 2,
            'service->updateTestTaker' => 0,
            'dataAggregator->countAllDeliveries' => 1,
            'dataAggregator->getSlice' => 2,
        ]);

        $login = '499 Tt';

        $regularStorage = new LocalStorage($this->service->reveal());
        $regularStorage->createStorage();

        $tmpStorage = new LocalStorage($this->service->reveal());
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

    public function testUpdateAffectedData()
    {
        $this->prepare([
            'service->setStorage' => 0,
            'service->hasOption' => 0,
            'service->getOptions' => 0,
            'service->setOptions' => 0,
            'service->updateTestTaker' => 2,
            'dataAggregator->countAllDeliveries' => 0,
            'dataAggregator->getSlice' => 0,
        ]);

        $login = '499 Tt';

        $regularStorage = new LocalStorage($this->service->reveal());
        $regularStorage->createStorage();
        $regularStorage->createRow($login);
        $regularStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $regularStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $regularStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $regularStorage->incrementField($login, StorageInterface::NB_ITEM);
        $regularStorage->incrementField($login, StorageInterface::NB_ITEM);
        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => '499 Tt',
            StorageInterface::NB_ITEM => 2,
            StorageInterface::NB_EXECUTIONS => 3,
            StorageInterface::NB_FINISHED => 0,
        ], $regularStorage->getRow($login));


        $tmpStorage = new LocalStorage($this->service->reveal());
        $tmpStorage->createStorage();
        $tmpStorage->createRow($login);
        $tmpStorage->incrementField($login, StorageInterface::NB_EXECUTIONS);
        $tmpStorage->incrementField($login, StorageInterface::NB_ITEM);
        $tmpStorage->incrementField($login, StorageInterface::NB_FINISHED);
        $tmpStorage->createRow('another user');
        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => '499 Tt',
            StorageInterface::NB_ITEM => 1,
            StorageInterface::NB_EXECUTIONS => 1,
            StorageInterface::NB_FINISHED => 1,
        ], $tmpStorage->getRow($login));

        $stub = new StubUpdater(
            $this->service->reveal(),
            $tmpStorage,
            $regularStorage,
            $this->dataAggregator->reveal()
        );
        $stub->setAggregator($this->dataAggregator->reveal());

        $this->assertEquals(2, $stub->stubUpdateAffectedData());
    }
}
