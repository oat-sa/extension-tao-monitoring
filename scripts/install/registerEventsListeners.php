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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
use oat\oatbox\service\ServiceManager;
use oat\oatbox\event\EventManager;


$serviceManager = ServiceManager::getServiceManager();
$eventManager = $serviceManager->get(EventManager::CONFIG_ID);

// count executions
$eventManager->attach(
    'oat\\taoDelivery\\models\\classes\\execution\\event\\DeliveryExecutionCreated',
    array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\Events', 'deliveryExecutionCreated')
);

// finished executions
$eventManager->attach(
    'oat\\taoDelivery\\models\\classes\\execution\\event\\DeliveryExecutionState',
    array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\Events', 'deliveryExecutionState')
);

// catch switch items - on switching recount all statistic for testtaker
$eventManager->attach(
    'oat\\taoQtiTest\\models\\event\\QtiMoveEvent',
    array('\\oat\\taoMonitoring\\model\\TestTakerDeliveryLog\\Events', 'qtiMoveEvent')
);

$serviceManager->register(EventManager::CONFIG_ID, $eventManager);
