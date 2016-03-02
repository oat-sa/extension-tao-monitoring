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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator;


use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoProctoring\model\EligibilityService;
use oat\taoProctoring\model\implementation\DeliveryService;
use tao_helpers_Date;
use taoDelivery_models_classes_execution_ServiceProxy;

/**
 * Recount all data in table
 *
 * for update data we had to switch storage to tmp, after update - fill all data to table
 *
 * Class Updater
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog
 */
class DataAggregator implements DataAggregatorInterface
{

    /**
     * @var DeliveryService
     */
    private $deliveryService;

    /**
     * @var EligibilityService
     */
    private $eligibilityService;

    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * @var null|\taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;

    /**
     * @var null|DeliveryAssemblyService
     */
    private $deliveryAssemblyService;

    /**
     * DataAggregator constructor.
     * @param DeliveryService $deliveryService
     * @param EligibilityService $eligibilityService
     * @param ResultsService $resultsService
     * @param taoDelivery_models_classes_execution_ServiceProxy|null $executionService
     * @param DeliveryAssemblyService|null $deliveryAssemblyService
     */
    public function __construct(
        DeliveryService $deliveryService,
        EligibilityService $eligibilityService,
        ResultsService $resultsService,

        taoDelivery_models_classes_execution_ServiceProxy $executionService = null,
        DeliveryAssemblyService $deliveryAssemblyService = null
    )
    {
        $this->deliveryService = $deliveryService;
        $this->eligibilityService = $eligibilityService;
        $this->resultsService = $resultsService;

        $this->executionService = $executionService;
        $this->deliveryAssemblyService = $deliveryAssemblyService;
    }

    public function countAllDeliveries()
    {
        if (!isset($this->deliveryAssemblyService)) {
            throw new \common_Exception('Incorrect deliveryAssemblyService');
        }

        return $this->deliveryAssemblyService->getRootClass()->countInstances();
    }
    
    /**
     * Collect all executions
     * 
     * @param int $page
     * @param int $inPage
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_NoImplementation
     */
    public function getSlice($page = 0, $inPage = 500)
    {
        if (!isset($this->executionService)) {
            throw new \common_Exception('Execution service should be set');
        }

        $slice = $this->deliveryAssemblyService->getRootClass()->searchInstances([], ['limit' => $inPage, 'offset' => $page * $inPage]);
        $executions = [];
        foreach ($slice as $delivery) {
            foreach ($this->executionService->getExecutionsByDelivery($delivery) as $execution) {
                array_push($executions, $execution);
            }
        }

        return $this->aggregation($executions);
    }

    /**
     * @param \core_kernel_classes_Resource $testCenter
     * @param \core_kernel_classes_Resource $delivery
     * @return array|\oat\taoDelivery\model\execution\DeliveryExecution[]
     */
    public function getData(\core_kernel_classes_Resource $testCenter = null, \core_kernel_classes_Resource $delivery = null)
    {
        $deliveryExecutions = $delivery
            ? $this->deliveryService->getCurrentDeliveryExecutions($delivery->getUri(), $testCenter->getUri())
            : $this->getTestCenterExecutions($testCenter);

        // sort all executions by reverse date
        usort($deliveryExecutions, function ($a, $b) {
            return -strcmp(tao_helpers_Date::getTimeStamp($a->getStartTime()), tao_helpers_Date::getTimeStamp($b->getStartTime()));
        });

        return $this->aggregation($deliveryExecutions);
    }

    private function getTestCenterExecutions(\core_kernel_classes_Resource $testCenter)
    {
        $deliveries = $this->eligibilityService->getEligibleDeliveries($testCenter);

        $all = [];
        foreach ($deliveries as $delivery) {
            if ($delivery->exists()) {
                $all = array_merge($all, $this->deliveryService->getCurrentDeliveryExecutions($delivery->getUri(), $testCenter->getUri()));
            }
        }

        return $all;
    }

    /**
     * Aggregate data from delivery executions
     *
     * @param $deliveryExecutions
     * @return array
     */
    private function aggregation($deliveryExecutions)
    {
        $aggregateData = [];
        foreach ($deliveryExecutions as $deliveryExecution) {

            $userId = $deliveryExecution->getUserIdentifier();
            if (!isset($aggregateData[$userId])) {

                $userResource = new \core_kernel_classes_Resource($userId);
                $user = new \core_kernel_users_GenerisUser($userResource);
                $login = current($user->getPropertyValues(PROPERTY_USER_LOGIN));

                $aggregateData[$userId] = [
                    'test_taker' => $login,
                    'nb_item' => 0,
                    'nb_executions' => 0,
                    'nb_finished' => 0
                ];
            }

            $rowResult = &$aggregateData[$userId];
            $rowResult['nb_executions']++;

            if ($deliveryExecution->getState()->getUri() === DeliveryExecution::STATE_FINISHIED) {
                $rowResult['nb_finished']++;
            }

            $rowResult['nb_item'] += $this->countFinishedItems($deliveryExecution);
        }

        return $aggregateData;
    }

    private function countFinishedItems(DeliveryExecution $deliveryExecution)
    {
        $implementation = $this->resultsService->getReadableImplementation($deliveryExecution->getDelivery());
        $this->resultsService->setImplementation($implementation);
        $itemCallIds = $this->resultsService->getItemResultsFromDeliveryResult($deliveryExecution);
        return count($itemCallIds);
    }
}
