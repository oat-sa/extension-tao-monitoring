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

namespace oat\taoMonitoring\test\TestTakerDeliveryLog\aggregator;


use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\TestTakerDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use Prophecy\Argument;

class ForTestTaker extends AbstractAggregator
{
    /**
     * @var \core_kernel_classes_Resource
     */
    private $testTaker;

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

        $this->executionService->getDeliveryExecutionsByStatus(Argument::type('string'), Argument::type('string'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['executionService->getDeliveryExecutionsByStatus'])
            ->willReturn([
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution->reveal(),
                $this->deliveryExecution2->reveal(),
            ]);
        
        /** @var \core_kernel_classes_Resource testTaker */
        $this->testTaker = $this->prophesize('\core_kernel_classes_Resource');
        $this->testTaker->getOnePropertyValue(Argument::type('\core_kernel_classes_Property'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['testTaker->getOnePropertyValue'])
            ->willReturn('TtLogin');
        $this->testTaker->getUri()
            ->shouldBeCalledTimes($shouldBeCalledTimes['testTaker->getUri'])
            ->willReturn('#uriTtLogin');
    }

    public function testGetAllData()
    {
        $this->prepare([
            'stateFinished->getUri' => 9,
            'statePaused->getUri' => 3,
            'deliveryExecution->getDelivery' => 9,
            'deliveryExecution->getUserIdentifier' => 9,
            'deliveryExecution->getState' => 9,
            'deliveryExecution2->getDelivery' => 3,
            'deliveryExecution2->getUserIdentifier' => 3,
            'deliveryExecution2->getState' => 3,
            'resultsService->getReadableImplementation' => 12,
            'resultsService->setImplementation' => 12,
            'resultsService->getItemResultsFromDeliveryResult' => 12,
            'executionService->getExecutionsByDelivery' => 1,
            'executionService->getDeliveryExecutionsByStatus' => 6,
            'testTaker->getOnePropertyValue' => 1,
            'testTaker->getUri' => 6,
        ]);
        
        $aggregator = new TestTakerDataAggregator(
            $this->resultsService->reveal(),
            $this->executionService->reveal(),
            $this->testTaker->reveal()
        );

        $count = $aggregator->countAllData();
        $this->assertEquals(12, $count);

        $data = $aggregator->getSlice(0, 20);
        $this->assertEquals(['#UserId' => [
            StorageInterface::NB_ITEM => 36,
            StorageInterface::NB_EXECUTIONS => 12,
            StorageInterface::NB_FINISHED => 9,
            StorageInterface::TEST_TAKER_LOGIN => false]]
            
            , $data);
    }
    
}
