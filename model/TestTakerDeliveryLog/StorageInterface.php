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


use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

interface StorageInterface extends DataAggregatorInterface
{
    /** Fields */
    // test taker login
    const TEST_TAKER_LOGIN = 'test_taker';
    // events
    const NB_ITEM = 'nb_item';
    const NB_EXECUTIONS = 'nb_executions';
    const NB_FINISHED = 'nb_finished';

    /**
     * StorageInterface constructor.
     * @param string
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
     * @return array
     */
    public function getRow($login = '');

    /**
     * Add new event to log
     * 
     * @param string $login
     * @param string $field
     * @return bool
     */
    public function incrementField($login = '', $field = '');

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
     * Add Or Create data in storage
     * # If test taker exists - add data to storage
     *  [
     *    'test_taker' => int,
     *    'nb_item' => int,
     *    'nb_executions' => int,
     *    'nb_finished' => int,
     *  ]
     * @param array $data
     */
    public function flushArray(array $data);

    /**
     * Replace or Create data in storage
     * @param array $data
     * @return mixed
     */
    public function replace(array $data);
}
