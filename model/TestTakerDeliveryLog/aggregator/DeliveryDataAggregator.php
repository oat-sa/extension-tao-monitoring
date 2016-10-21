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


use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoOutcomeUi\model\ResultsService;
use taoDelivery_models_classes_execution_ServiceProxy;

/**
 * Recount all data in table by deliveries
 *
 * for update data we had to switch storage to tmp, after update - fill all data to table
 *
 * Class Updater
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog
 */
class DeliveryDataAggregator extends AbstractDataAggregator
{
    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;

    /**
     * @var DeliveryAssemblyService
     */
    private $deliveryAssemblyService;

    /**
     * @var ResultsService
     */
    private $resultsService;

    public function __construct(
        ResultsService $resultsService,
        taoDelivery_models_classes_execution_ServiceProxy $executionService,
        DeliveryAssemblyService $deliveryAssemblyService)
    {
        $this->resultsService = $resultsService;
        $this->executionService = $executionService;
        $this->deliveryAssemblyService = $deliveryAssemblyService;
    }

    public function countAllData()
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
        $slice = $this->deliveryAssemblyService->getRootClass()->searchInstances([], ['limit' => $inPage, 'offset' => $page * $inPage]);
        $executions = [];

        $monitor = $this->executionService->implementsMonitoring();
        foreach ($slice as $delivery) {
            if($monitor){
                foreach ($this->executionService->getExecutionsByDelivery($delivery) as $execution) {
                    array_push($executions, $execution);
                }
            } else{
                $implementation = $this->resultsService->getReadableImplementation($delivery);
                foreach ($implementation->getResultByDelivery(array($delivery->getUri())) as $res) {
                    $execution = $this->executionService->getDeliveryExecution($res['deliveryResultIdentifier']);
                    array_push($executions, $execution);
                }
            }
        }
        return $this->aggregation($executions);
    }
}
