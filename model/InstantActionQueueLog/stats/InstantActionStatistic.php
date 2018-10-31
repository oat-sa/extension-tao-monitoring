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

namespace oat\taoMonitoring\model\InstantActionQueueLog\stats;


use oat\taoMonitoring\model\InstantActionQueueLog\storage\InstantActionQueueLogRdsStorage;

class InstantActionStatistic extends InstantActionQueueLogRdsStorage
{

    /**
     * @param string $deliveryUri
     * @return mixed
     * @throws \common_Exception
     */
    public function getRow($deliveryUri = '')
    {
        if (!$deliveryUri) {
            throw new \common_Exception('DeliveryLogRdsStorage should have deliveryUri');
        }

        $sql = "SELECT * FROM " . self::TABLE_NAME ." WHERE " . self::DELIVERY . "=? ";

        $parameters = [$deliveryUri];
        $stmt = $this->getPersistence()->query($sql, $parameters);

        return current($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * The enter and exit times of each user in the login queue is reported at an individual level (each impacted registration id is provided)
     * @param int $from unix time
     * @param int $to unix time
     * @return array
     */
    public function getQueuedUsers($from, $to)
    {
        $sql = "SELECT ".self::PARAM_QUEUE_KEY.", ".self::PARAM_USER_ID.", MAX(" . self::PARAM_ACTION_TIME . "), MIN(" . self::PARAM_ACTION_TIME . ") FROM " . self::TABLE_NAME ." WHERE "
            . self::PARAM_ACTION_TIME . " >= ? AND "
            . self::PARAM_ACTION_TIME . " <= ? "
            . "GROUP BY ".self::PARAM_QUEUE_KEY. ",". self::PARAM_USER_ID;

        $parameters = [$from, $to];
        $stmt = $this->getPersistence()->query($sql, $parameters);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Number of times the queue was initiated
     * @param $from
     * @param $to
     * @return int
     */
    public function getNumberOfQueueInitiations($from, $to)
    {
        $sql = "SELECT COUNT(DISTINCT ".self::PARAM_QUEUE_KEY.") FROM " . self::TABLE_NAME . " WHERE "
            . self::PARAM_ACTION_TIME . " >= ?"
            . " AND " . self::PARAM_ACTION_TIME . " <= ?"
            . " AND " . self::PARAM_ACTION_TYPE . " = ?";

        $parameters = [$from, $to, 'queue'];
        $stmt = $this->getPersistence()->query($sql, $parameters);

        return current($stmt->fetchAll(\PDO::FETCH_ASSOC))['count'];
    }

    /**
     * Minimum duration a user was in the queue
     * @param $from
     * @param $to
     * @return mixed
     */
    public function minimumDurationInQueue($from, $to)
    {
        $sql = "SELECT ".self::PARAM_QUEUE_KEY.", ".self::PARAM_USER_ID.", MAX(" . self::PARAM_ACTION_TIME . ") - MIN(" . self::PARAM_ACTION_TIME . ") AS duration FROM " . self::TABLE_NAME ." WHERE "
            . self::PARAM_ACTION_TIME . " >= ? AND "
            . self::PARAM_ACTION_TIME . " <= ? "
            . "GROUP BY ".self::PARAM_QUEUE_KEY. ",". self::PARAM_USER_ID;

        $parameters = [$from, $to];
        $stmt = $this->getPersistence()->query($sql, $parameters);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
