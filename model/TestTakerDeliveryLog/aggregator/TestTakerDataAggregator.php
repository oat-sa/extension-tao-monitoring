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
use oat\taoOutcomeUi\model\ResultsService;

/**
 * Get statistics by TestTaker
 * 
 * Class TestTakerDataAggregator
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator
 */
class TestTakerDataAggregator extends AbstractDataAggregator
{

    /**
     * @var \core_kernel_classes_Resource
     */
    private $testTaker;

    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;
    
    /**
     * @var string
     */
    private $login = '';
    
    public function __construct(
        ResultsService $resultsService,
        \taoDelivery_models_classes_execution_ServiceProxy $executionService,
        \core_kernel_classes_Resource $testTaker
    ) {
        parent::__construct($resultsService);

        $this->testTaker = $testTaker;
        $this->login =  $testTaker->getOnePropertyValue(new \core_kernel_classes_Property(PROPERTY_USER_LOGIN));
        if (!$this->login) {
            throw new \common_Exception('Test taker should be correct user resource');
        }
        
        $this->executionService = $executionService;
    }

    /**
     * Count of the executions
     */
    public function countAllData()
    {
        $count = 0;
        
        // all executions
        foreach ([DeliveryExecution::STATE_ACTIVE, DeliveryExecution::STATE_FINISHIED, DeliveryExecution::STATE_PAUSED] as $state) {
            $res = $this->executionService->getDeliveryExecutionsByStatus($this->testTaker->getUri(), $state);
            if (is_array($res)) {
                $count += count($res);
            }
        }
        
        return $count;
    }
    
    public function getSlice($page = 0, $inPage = 0)
    {
        $executions = [];
        foreach ([DeliveryExecution::STATE_ACTIVE, DeliveryExecution::STATE_FINISHIED, DeliveryExecution::STATE_PAUSED] as $state) {
            $res = $this->executionService->getDeliveryExecutionsByStatus($this->testTaker->getUri(), $state);
            if (is_array($res)) {
                foreach ($res as $row) {
                    array_push($executions, $row);
                }
            }
        }
        return $this->aggregation($executions);
    }
}
