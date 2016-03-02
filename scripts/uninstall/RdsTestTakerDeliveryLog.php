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

namespace oat\taoMonitoring\scripts\uninstall\Delivery;


use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class RdsTestTakerDeliveryLog extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        
        if ($this->getServiceManager()->has(TestTakerDeliveryLogInterface::SERVICE_ID)) {

            /** Uninstall rds storage */
            $storage = new RdsStorage( $this->getServiceManager()->get(TestTakerDeliveryLogInterface::SERVICE_ID) );
            $storage->dropStorage();

            $this->registerService(TestTakerDeliveryLogInterface::SERVICE_ID, null);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery log for test taker'));
    }
}
