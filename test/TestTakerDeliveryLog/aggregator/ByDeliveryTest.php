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


use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\DeliveryDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\test\TestTakerDeliveryLog\aggregator\AbstractAggregator;
use Prophecy\Argument;

class ByDeliveryTest extends AbstractAggregator
{
    
    /**
     * @var \oat\taoDeliveryRdf\model\DeliveryAssemblyService
     */
    private $deliveryAssemblyService;


    /**
     * @param array $shouldBeCalledTimes
     *  [
     *      'resource->getUri' => int
     *      'deliveryService->getCurrentDeliveryExecutions' => int,
     *
     *  ]
     */
    protected function prepare(array $shouldBeCalledTimes)
    {
        parent::prepare($shouldBeCalledTimes);

        $this->executionService->getExecutionsByDelivery(Argument::type('\core_kernel_classes_Resource'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['executionService->getExecutionsByDelivery'])
            ->willReturn([
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution2->reveal(),
            ]);

        $this->executionService->implementsMonitoring()
            ->shouldBeCalledTimes($shouldBeCalledTimes['executionService->implementsMonitoring'])
            ->willReturn(true);
        
        /** @var \core_kernel_classes_Class $class */
        $class = $this->prophesize('\core_kernel_classes_Class');

        $class->countInstances(Argument::any(), Argument::any())
            ->shouldBeCalledTimes($shouldBeCalledTimes['class->countInstances'])
            ->willReturn(4);

        $class->searchInstances(Argument::type('array'), Argument::type('array'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['class->searchInstances'])
            ->willReturn([
                $this->delivery->reveal(),
                $this->delivery->reveal(),
                $this->delivery->reveal(),
                $this->delivery->reveal(),
            ]);

        /** @var \oat\taoDeliveryRdf\model\DeliveryAssemblyService deliveryAssemblyService */
        $this->deliveryAssemblyService = $this->prophesize('\oat\taoDeliveryRdf\model\DeliveryAssemblyService');
        $this->deliveryAssemblyService->getRootClass()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryAssemblyService->getRootClass'])
            ->willReturn($class);
    }

    public function testGetAllData()
    {
        $this->prepare([
            'delivery->exists' => 0,
            'stateFinished->getUri' => 12,
            'statePaused->getUri' => 4,
            'deliveryExecution->getStartTime' => 0,
            'deliveryExecution->getUserIdentifier' => 12,
            'deliveryExecution->getState' => 12,
            'deliveryExecution->getIdentifier' => 0,
            'deliveryExecution2->getUserIdentifier' => 4,
            'deliveryExecution2->getState' => 4,
            'deliveryExecution2->getIdentifier' => 0,
            'class->countInstances' => 1,
            'class->searchInstances' => 1,
            'deliveryAssemblyService->getRootClass' => 2,
            'executionService->getExecutionsByDelivery' => 4,
            'executionService->implementsMonitoring' => 1,
        ]);

        $aggregator = new DeliveryDataAggregator(
            $this->resultsService->reveal(),
            $this->executionService->reveal(),
            $this->deliveryAssemblyService->reveal()
        );

        $count = $aggregator->countAllData();
        $this->assertEquals(4, $count);

        $data = $aggregator->getSlice(0, 20);
        $this->assertEquals(['#UserId' => [
            StorageInterface::NB_EXECUTIONS => 16,
            StorageInterface::NB_FINISHED => 12, 
            StorageInterface::TEST_TAKER_LOGIN => false]], $data);
    }
}
