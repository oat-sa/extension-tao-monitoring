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
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

abstract class AbstractDataAggregator implements DataAggregatorInterface
{

    /**
     * Aggregate data from delivery executions
     *
     * @param $deliveryExecutions
     * @return array
     */
    protected function aggregation($deliveryExecutions)
    {
        $aggregateData = [];
        /** @var \taoDelivery_models_classes_execution_DeliveryExecution $deliveryExecution */
        foreach ($deliveryExecutions as $deliveryExecution) {

            $userId = $deliveryExecution->getUserIdentifier();
            if (!isset($aggregateData[$userId])) {

                $userResource = new \core_kernel_classes_Resource($userId);
                $user = new \core_kernel_users_GenerisUser($userResource);
                $login = current($user->getPropertyValues(PROPERTY_USER_LOGIN));

                $aggregateData[$userId] = [
                    StorageInterface::TEST_TAKER_LOGIN => $login,
                    StorageInterface::NB_EXECUTIONS => 0,
                    StorageInterface::NB_FINISHED => 0
                ];
            }

            $rowResult = &$aggregateData[$userId];
            $rowResult[StorageInterface::NB_EXECUTIONS]++;

            if ($deliveryExecution->getState()->getUri() === DeliveryExecution::STATE_FINISHIED) {
                $rowResult[StorageInterface::NB_FINISHED]++;
            }
        }

        return $aggregateData;
    }

} 
