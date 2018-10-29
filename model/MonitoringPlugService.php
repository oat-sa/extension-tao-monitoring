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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoMonitoring\model;


use oat\oatbox\service\ConfigurableService;

/**
 * Service to control state of the monitoring (turn on / turn off)
 *
 * Active services of the statistics stored as a configurable properties of this service
 * # we can't store such data to anywhere else, because of performance issue
 * ## for example storing in the DB will add extra 'SELECT' query to getting state of service
 *
 * Class MonitoringPluggableService
 * @package oat\taoMonitoring\model
 */
class MonitoringPlugService extends ConfigurableService
{
    const SERVICE_ID = 'taoMonitoring/MonitoringPlugService';

    /**
     * @param string $serviceId
     * @return bool
     */
    public function isServiceActive($serviceId = '')
    {
        return in_array($serviceId, $this->getOption('services'));
    }
}
