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

namespace oat\taoMonitoring\scripts\install;


use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;

/**
 * 
 * 
 * Class RegisterTestTakerLog
 * @package oat\taoMonitoring\scripts\install\Delivery
 */
class RegisterRdsTestTakerDeliveryLog extends \common_ext_action_InstallAction
{
    
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';

        /** Register new service */
        $this->registerService(Service::SERVICE_ID, new Service([RdsStorage::OPTION_PERSISTENCE => $persistenceId]));

        /** @var RdsStorage $storage */
        $storage = new RdsStorage( $this->getServiceManager()->get(Service::SERVICE_ID) );
        $storage->createStorage();
        
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery log for test taker'));
    }
}
