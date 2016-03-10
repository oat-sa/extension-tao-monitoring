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
use oat\taoMonitoring\model\TestTakerDeliveryLog\DataAggregatorInterface;
use oat\taoOutcomeUi\model\ResultsService;

abstract class AbstractDataAggregator implements DataAggregatorInterface
{

    /**
     * @var ResultsService
     */
    private $resultsService;

    public function __construct(ResultsService $resultsService)
    {
        $this->resultsService = $resultsService;

    }

    /**
     * Aggregate data from delivery executions
     *
     * @param $deliveryExecutions
     * @return array
     */
    protected function aggregation($deliveryExecutions)
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
