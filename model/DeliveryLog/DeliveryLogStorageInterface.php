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

namespace oat\taoMonitoring\model\DeliveryLog;


interface DeliveryLogStorageInterface
{
    /** Fields */
    const DELIVERY = 'delivery';
    // events
    const NB_EXECUTIONS = 'nb_executions';
    const NB_FINISHED = 'nb_finished';

    /**
     * DeliveryLogStorageInterface constructor.
     * @param string $param
     */
    public function __construct($param = '');

    /**
     * Create new log record
     *
     * @param string $login
     * @return bool
     */
    public function createRow($login = '');

    /**
     * Get row
     *
     * @param string $login
     * @return array|bool
     */
    public function getRow($login = '');

    /**
     * Create storage
     * @return string (table name or file path)
     */
    public function createStorage();

    /**
     * Destroy storage
     * @return bool
     */
    public function dropStorage();

    /**
     * @param string $deliveryId
     * @return bool
     */
    public function addExecution($deliveryId = '');

    /**
     * @param string $deliveryId
     * @return bool
     */
    public function addFinishedExecution($deliveryId = '');
}
