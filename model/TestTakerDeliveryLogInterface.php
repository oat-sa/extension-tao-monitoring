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

namespace oat\taoMonitoring\model;


/**
 * 
 * Interface TestTakerLogInterface
 * @package oat\taoMonitoring\model\Delivery
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */
interface TestTakerDeliveryLogInterface
{
    const SERVICE_ID = 'taoMonitoring/testTakerDeliveryLog';

    /** Fields */
    const TEST_TAKER_LOGIN = 'test_taker';
    // events
    const NB_ITEM = 'nb_item';
    const NB_EXECUTIONS = 'nb_executions';
    const NB_FINISHED = 'nb_finished';

    /**
     * Increment test taker event
     * (create row if not exists)
     *
     * @param string $testTakerLogin
     * @param string $nb_event
     * @return bool
     */
    public function logEvent($testTakerLogin = '', $nb_event = '');

    
    /**
     * Recount all log data
     * 
     * # lock log DB
     * # truncate log DB
     * # recount all data from sources
     * # unlock DB 
     * 
     * @return bool
     */
    public function upgrade();
}
