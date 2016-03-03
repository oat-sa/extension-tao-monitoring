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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog;


use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoQtiTest\models\event\QtiMoveEvent;

interface EventInterface
{
    /**
     * execution started
     * @param DeliveryExecutionCreated $event
     * @return mixed
     */
    public static function deliveryExecutionCreated(DeliveryExecutionCreated $event);
    
    /**
     * for Delivery status (Finished)
     * @param DeliveryExecutionState $event
     * @return mixed
     */
    public static function deliveryExecutionState(DeliveryExecutionState $event);

    /**
     * for count of the finished items
     * @param QtiMoveEvent $event
     * @return mixed
     */
    public static function qtiMoveEvent(QtiMoveEvent $event);
}
