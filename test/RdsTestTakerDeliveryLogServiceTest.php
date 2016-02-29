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

namespace oat\taoMonitoring\test;


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\implementation\RdsTestTakerDeliveryLogService;

class RdsTestTakerDeliveryLogServiceTest extends TaoPhpUnitTestRunner
{

    /**
     * @var RdsTestTakerDeliveryLogService
     */
    private $service;
    
    public function setUp()
    {
        parent::setUp();
        TaoPhpUnitTestRunner::initTest();
        
        $this->service = new RdsTestTakerDeliveryLogService(['persistence' => 'default']);
    }

    /**
     * @expectedException \common_Exception
     */
    public function testLogEventWithoutLogin()
    {
        $this->service->logEvent();
    }

    /**
     * @expectedException \common_Exception
     */    
    public function testLogEventWithoutNbField()
    {
        $this->service->logEvent('tt1');
    }

    public function testLogEvent()
    {
        $this->service->logEvent('tt1', RdsTestTakerDeliveryLogService::NB_ITEM);
    }
}
