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

namespace oat\taoMonitoring\model\implementation;


use oat\oatbox\service\ConfigurableService;
//use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class RdsTestTakerDeliveryLogService extends ConfigurableService 
    //implements TestTakerDeliveryLogInterface
{
    
    const TABLE_NAME = 'monitoring_testtaker_deliveries';
    const OPTION_PERSISTENCE = 'persistence';

    public function logEvent($testTakerLogin = '', $nb_event = '')
    {
        
    }

    public function lock()
    {
        // TODO: Implement lock() method.
    }

    public function unlock()
    {
        // TODO: Implement unlock() method.
    }

    public function upgrade()
    {
        // TODO: Implement upgrade() method.
    }
}
