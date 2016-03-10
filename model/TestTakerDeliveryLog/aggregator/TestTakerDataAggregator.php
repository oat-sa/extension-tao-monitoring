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


use core_kernel_classes_Class;
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
     * @var string
     */
    private $testTakerUri;
    
    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;
    
    public static function factory( $login = '' ) {
        
        $class = new core_kernel_classes_Class(CLASS_GENERIS_USER);
        $users = $class->searchInstances(
            array(PROPERTY_USER_LOGIN => $login),
            array('like' => false, 'recursive' => true)
        );

        if (!count($users)) {
            return false;
        }

        $testTakerUri = current($users)->getUri();

        return new TestTakerDataAggregator (
            ResultsService::singleton(),
            \taoDelivery_models_classes_execution_ServiceProxy::singleton(),
            $testTakerUri
        );
    }
    
    public function __construct(
        ResultsService $resultsService,
        \taoDelivery_models_classes_execution_ServiceProxy $executionService,
        $testTakerUri = ''
    ) {
        parent::__construct($resultsService);

        $this->testTakerUri = $testTakerUri;
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
            $res = $this->executionService->getDeliveryExecutionsByStatus($this->testTakerUri, $state);
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
            $res = $this->executionService->getDeliveryExecutionsByStatus($this->testTakerUri, $state);
            if (is_array($res)) {
                foreach ($res as $row) {
                    array_push($executions, $row);
                }
            }
        }
        
        return $this->aggregation($executions);
    }
}
