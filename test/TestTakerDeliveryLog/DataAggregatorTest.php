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
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\DataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use Prophecy\Argument;

define('CLASS_COMPILEDDELIVERY', 'http://www.tao.lu/Ontologies/TAODelivery.rdf#AssembledDelivery');

class DataAggregatorTest extends TaoPhpUnitTestRunner
{

    /**
     * @var \oat\taoProctoring\model\implementation\DeliveryService
     */
    private $deliveryService;

    /**
     * @var \oat\taoProctoring\model\EligibilityService
     */
    private $eligibilityService;

    /**
     * @var \oat\taoOutcomeUi\model\ResultsService
     */
    private $resultsService;

    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;

    /**
     * @var \oat\taoDeliveryRdf\model\DeliveryAssemblyService
     */
    private $deliveryAssemblyService;
    
    /**
     * @var \core_kernel_classes_Resource
     */
    private $testCenter;

    /**
     * @var \core_kernel_classes_Resource
     */
    private $delivery;

    public function setUp()
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();
    }
    
    /**
     * @param array $shouldBeCalledTimes
     *  [
     *      'resource->getUri' => int
     *      'deliveryService->getCurrentDeliveryExecutions' => int,
     *
     *  ]
     */
    private function prepare(array $shouldBeCalledTimes)
    {
        /** @var \core_kernel_classes_Resource testCenter */
        $this->testCenter = $this->prophesize('\core_kernel_classes_Resource');
        $this->testCenter->getUri()
            ->shouldBeCalledTimes($shouldBeCalledTimes['testCenter->getUri'])
            ->willReturn('#testCenterUri');
        
        /** @var \core_kernel_classes_Resource delivery */
        $this->delivery = $this->prophesize('\core_kernel_classes_Resource');
        $this->delivery->exists()
            ->shouldBeCalledTimes($shouldBeCalledTimes['delivery->exists'])
            ->willReturn(true);
        $this->delivery
            ->getUri()
            ->shouldBeCalledTimes($shouldBeCalledTimes['delivery->getUri'])
            ->willReturn('#deliveryUri');


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
        $deliveryExecution = $this->prophesize('\oat\taoDelivery\models\classes\execution\DeliveryExecution');
        $deliveryExecution->getStartTime()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getStartTime'])
            ->willReturn(time());
        $deliveryExecution->getDelivery()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getDelivery'])
            ->willReturn($this->delivery->reveal());
        $deliveryExecution->getUserIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getUserIdentifier'])
            ->willReturn('#UserId');
        $deliveryExecution->getState()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution->getState'])
            ->willReturn($stateFinished->reveal());
        
        /** @var DeliveryExecution $deliveryExecution2 */
        $deliveryExecution2 = $this->prophesize('\oat\taoDelivery\models\classes\execution\DeliveryExecution');
        $deliveryExecution2->getStartTime()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getStartTime'])
            ->willReturn(time());
        $deliveryExecution2->getDelivery()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getDelivery'])
            ->willReturn($this->delivery->reveal());
        $deliveryExecution2->getUserIdentifier()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getUserIdentifier'])
            ->willReturn('#UserId');
        $deliveryExecution2->getState()
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryExecution2->getState'])
            ->willReturn($statePaused->reveal());
        
        /** @var \oat\taoProctoring\model\implementation\DeliveryService deliveryService */
        $this->deliveryService = $this->prophesize('\oat\taoProctoring\model\implementation\DeliveryService');
        $this->deliveryService
            ->getCurrentDeliveryExecutions(Argument::type('string'), Argument::type('string'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['deliveryService->getCurrentDeliveryExecutions'])
            ->willReturn([
                $deliveryExecution->reveal(),   
                $deliveryExecution->reveal(),
                $deliveryExecution->reveal(),
                $deliveryExecution2->reveal(),   
            ]);
        
        /** @var \oat\taoProctoring\model\EligibilityService eligibilityService */
        $this->eligibilityService = $this->prophesize('\oat\taoProctoring\model\EligibilityService');
        $this->eligibilityService->getEligibleDeliveries(Argument::type('\core_kernel_classes_Resource'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['eligibilityService->getEligibleDeliveries'])
            ->willReturn([
                $this->delivery->reveal(),
                $this->delivery->reveal(),
                $this->delivery->reveal(),
            ]);

        /** @var \oat\taoResultServer\models\classes\ResultManagement $resultManagement */
        $resultManagement = $this->prophesize('\oat\taoResultServer\models\classes\ResultManagement');
        
        /** @var \oat\taoOutcomeUi\model\ResultsService resultsService */
        $this->resultsService = $this->prophesize('\oat\taoOutcomeUi\model\ResultsService');
        //mock
        $this->resultsService->getReadableImplementation(Argument::any())
            ->shouldBeCalledTimes($shouldBeCalledTimes['resultsService->getReadableImplementation'])
            ->willReturn($resultManagement->reveal());
        //mock
        $this->resultsService->setImplementation(Argument::type('\oat\taoResultServer\models\classes\ResultManagement'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['resultsService->setImplementation'])
            ->willReturn(true);
        $this->resultsService->getItemResultsFromDeliveryResult(Argument::type('\oat\taoDelivery\models\classes\execution\DeliveryExecution'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['resultsService->getItemResultsFromDeliveryResult'])
            ->willReturn([1,2,3]);
        
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
        
        /** @var \taoDelivery_models_classes_execution_ServiceProxy executionService */
        $this->executionService = $this->prophesize('\taoDelivery_models_classes_execution_ServiceProxy');
        $this->executionService->getExecutionsByDelivery(Argument::type('\core_kernel_classes_Resource'))
            ->shouldBeCalledTimes($shouldBeCalledTimes['executionService->getExecutionsByDelivery'])
            ->willReturn([
                $deliveryExecution->reveal(),
                $deliveryExecution->reveal(),
                $deliveryExecution->reveal(),
                $deliveryExecution2->reveal(),
            ]);
    }
    
    public function testGetDeliveryData()
    {
        $this->prepare([
            'testCenter->getUri' => 1,
            'stateFinished->getUri' => 3,
            'statePaused->getUri' => 1,
            'delivery->exists' => 0,
            'delivery->getUri' => 1,
            'deliveryService->getCurrentDeliveryExecutions' => 1,
            'deliveryExecution->getStartTime' => 6,
            'deliveryExecution->getUserIdentifier' => 3,
            'deliveryExecution->getState' => 3,
            'deliveryExecution->getDelivery' => 3,
            'deliveryExecution2->getStartTime' => 2,
            'deliveryExecution2->getUserIdentifier' => 1,
            'deliveryExecution2->getState' => 1,
            'deliveryExecution2->getDelivery' => 1,
            'eligibilityService->getEligibleDeliveries' => 0,
            'resultsService->getReadableImplementation' => 4,
            'resultsService->setImplementation' => 4,
            'resultsService->getItemResultsFromDeliveryResult' => 4,
            'class->countInstances' => 0,
            'class->searchInstances' => 0,
            'deliveryAssemblyService->getRootClass' => 0,
            'executionService->getExecutionsByDelivery' => 0,
        ]);
        
        $aggregator = new DataAggregator(
            $this->deliveryService->reveal(),
            $this->eligibilityService->reveal(),
            $this->resultsService->reveal()
        );
        
        $data = $aggregator->getData($this->testCenter->reveal(), $this->delivery->reveal());
        $this->assertEquals(['#UserId' => [StorageInterface::NB_ITEM => 12, StorageInterface::NB_EXECUTIONS => 4, StorageInterface::NB_FINISHED => 3, StorageInterface::TEST_TAKER_LOGIN => false]], $data);
    }

    public function testGetTestCenterData()
    {
        $this->prepare([
            'testCenter->getUri' => 3,
            'delivery->getUri' => 3,
            'stateFinished->getUri' => 9,
            'statePaused->getUri' => 3,
            'deliveryExecution->getStartTime' => 38,
            'deliveryExecution->getUserIdentifier' => 9,
            'deliveryExecution->getDelivery' => 9,
            'deliveryExecution2->getStartTime' => 12,
            'deliveryExecution->getState' => 9,
            'deliveryExecution2->getUserIdentifier' => 3,
            'deliveryExecution2->getState' => 3,
            'deliveryExecution2->getDelivery' => 3,
            'deliveryService->getCurrentDeliveryExecutions' => 3,
            'eligibilityService->getEligibleDeliveries' => 1,
            'delivery->exists' => 3,
            'resultsService->getReadableImplementation' => 12,
            'resultsService->setImplementation' => 12,
            'resultsService->getItemResultsFromDeliveryResult' => 12,
            'class->countInstances' => 0,
            'class->searchInstances' => 0,
            'deliveryAssemblyService->getRootClass' => 0,
            'executionService->getExecutionsByDelivery' => 0,
        ]);

        $aggregator = new DataAggregator(
            $this->deliveryService->reveal(),
            $this->eligibilityService->reveal(),
            $this->resultsService->reveal()
        );

        $data = $aggregator->getData($this->testCenter->reveal());
        $this->assertEquals(['#UserId' => ['nb_item' => 36, 'nb_executions' => 12, 'nb_finished' => 9, 'test_taker' => false]], $data);
    }

    public function testGetAllDeliveriesData()
    {
        $this->prepare([
            'testCenter->getUri' => 0,
            'delivery->getUri' => 0,
            'stateFinished->getUri' => 12,
            'statePaused->getUri' => 4,
            'deliveryExecution->getStartTime' => 0,
            'deliveryExecution->getUserIdentifier' => 12,
            'deliveryExecution->getDelivery' => 12,
            'deliveryExecution2->getStartTime' => 0,
            'deliveryExecution->getState' => 12,
            'deliveryExecution2->getUserIdentifier' => 4,
            'deliveryExecution2->getState' => 4,
            'deliveryExecution2->getDelivery' => 4,
            'deliveryService->getCurrentDeliveryExecutions' => 0,
            'eligibilityService->getEligibleDeliveries' => 0,
            'delivery->exists' => 0,
            'resultsService->getReadableImplementation' => 16,
            'resultsService->setImplementation' => 16,
            'resultsService->getItemResultsFromDeliveryResult' => 16,
            'class->countInstances' => 1,
            'class->searchInstances' => 1,
            'deliveryAssemblyService->getRootClass' => 2,
            'executionService->getExecutionsByDelivery' => 4,
        ]);

        $aggregator = new DataAggregator(
            $this->deliveryService->reveal(),
            $this->eligibilityService->reveal(),
            $this->resultsService->reveal(),
            $this->executionService->reveal(),
            $this->deliveryAssemblyService->reveal()
        );

        $count = $aggregator->countAllDeliveries();
        $this->assertEquals(4, $count);
        
        $data = $aggregator->getSlice(0, 20);
        $this->assertEquals(['#UserId' => [StorageInterface::NB_ITEM => 48, StorageInterface::NB_EXECUTIONS => 16, StorageInterface::NB_FINISHED => 12, StorageInterface::TEST_TAKER_LOGIN => false]], $data);
    }
}
