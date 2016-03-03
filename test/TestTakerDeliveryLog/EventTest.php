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
use oat\taoMonitoring\model\TestTakerDeliveryLog\event\Events;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\test\TestTakerDeliveryLog\Mock\TestStorage;

class EventTest extends TaoPhpUnitTestRunner
{
    /** @var  Service */
    private $service;
    
    /** @var  TestStorage */
    private $storage;
    
    public function setUp()
    {
        parent::setUp();

        $this->service = new Service(['persistence' => 'default']);

        $this->storage = new TestStorage($this->service);
        $this->storage->createStorage();
        $this->service->setStorage($this->storage);
    }

    public function testDeliveryExecutionCreated()
    {
        /*
         * TODO How can I tested UserHelper::getUser?
         * Events::setService($this->service);
        
        $deliveryExecution = $this->prophesize('\oat\taoDelivery\model\execution\DeliveryExecution');
        $deliveryExecution->getUserIdentifier()
            ->shouldBeCalledTimes(1)
            ->willReturn('#idUser');
        
        $event = $this->prophesize('\oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated');
        $event->getDeliveryExecution()
            ->shouldBeCalledTimes(1)
            ->willReturn($deliveryExecution);
        
        Events::deliveryExecutionCreated($event->reveal());*/
    }
    
    public function testDeliveryExecutionState()
    {
        
    }
    
    public function testQtiMoveEvent()
    {
        
    }
}
