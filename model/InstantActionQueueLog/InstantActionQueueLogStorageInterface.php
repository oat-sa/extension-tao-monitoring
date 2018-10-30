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

namespace oat\taoMonitoring\model\InstantActionQueueLog;


interface InstantActionQueueLogStorageInterface
{

    const PARAM_QUEUE_KEY = 'queue_key';
    const PARAM_USER_ID = 'user_id';
    const PARAM_ACTION_TYPE = 'action_type'; // queue, dequeue
    const PARAM_ACTION_TIME = 'action_time';
    const PARAM_RESOURCE_ID = 'resource_id';

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
}
