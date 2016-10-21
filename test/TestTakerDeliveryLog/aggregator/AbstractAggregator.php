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


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoDelivery\model\execution\DeliveryExecution;

abstract class AbstractAggregator extends TaoPhpUnitTestRunner
{

    /**
     * @var \core_kernel_classes_Resource
     */
    protected $delivery;

    /**
     * @var \oat\taoOutcomeUi\model\ResultsService
     */
    protected $resultsService;

    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    protected $executionService;

    /** @var  DeliveryExecution */
    protected $deliveryExecution;

    /** @var  DeliveryExecution */
    protected $deliveryExecution2;


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
        /** @var \core_kernel_classes_Resource delivery */
        $this->delivery = $this->prophesize('\core_kernel_classes_Resource');
        $this->delivery->exists()
            ->shouldBeCalledTimes($shouldBeCalledTimes['delivery->exists'])
            ->willReturn(true);

        /** @var \core_kernel_classes_Resource $stateFinished */
        $stateFinished = $this->prophesize('\core_kernel_classes_Resource');
        $stateFinished
            ->getUri()
            ->shouldBeCalledTimes($shouldBeCalledTimes['stateFinished->getUri'])
            ->willReturn(DeliveryExecution::STATE_FINISHIED);

        /** @var \core_kernel_classes_Resource $statePaused */
        $statePaused = $this->prophesize('\core_kernel_classes_Resource');
        $statePaused
            ->getUri()
            ->shouldBeCalledTimes($shouldBeCalledTimes['statePaused->getUri'])
            ->willReturn(DeliveryExecution::STATE_PAUSED);

        /** @var DeliveryExecution $deliveryExecution */
        $this->deliveryExecution = $this->prophesize('\oat\taoDelivery\models\classes\execution\DeliveryExecution');
        $this->deliveryExecution->getUserIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getUserIdentifier'])
            ->willReturn('#UserId');
        $this->deliveryExecution->getState()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getState'])
            ->willReturn($stateFinished->reveal());
        $this->deliveryExecution->getIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getIdentifier'])
            ->willReturn('#resultIdentifier');

        /** @var DeliveryExecution $deliveryExecution2 */
        $this->deliveryExecution2 = $this->prophesize('\oat\taoDelivery\models\classes\execution\DeliveryExecution');
        $this->deliveryExecution2->getUserIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getUserIdentifier'])
            ->willReturn('#UserId');
        $this->deliveryExecution2->getState()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getState'])
            ->willReturn($statePaused->reveal());
        $this->deliveryExecution2->getIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getIdentifier'])
            ->willReturn('#resultIdentifier2');

        /** @var \oat\taoOutcomeUi\model\ResultsService resultsService */
        $this->resultsService = $this->prophesize('\oat\taoOutcomeUi\model\ResultsService');

        /** @var \taoDelivery_models_classes_execution_ServiceProxy executionService */
        $this->executionService = $this->prophesize('\taoDelivery_models_classes_execution_ServiceProxy');
    }

    abstract public function testGetAllData();
}
