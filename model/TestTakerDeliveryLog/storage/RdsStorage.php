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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog\storage;


use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

class RdsStorage implements StorageInterface
{

    const TABLE_NAME = 'monitoring_testtaker_deliveries';

    /**
     * @var
     */
    private $persistence;
    
    public function __construct($persistence)
    {
        $this->persistence = $persistence;
    }
    
    /**
     * Create new test takers log
     * @param string $login
     * @return bool
     */
    public function create($login = '')
    {
        $result = $this->getPersistence()->insert(self::TABLE_NAME, [
            self::TEST_TAKER_LOGIN => $login,
            self::NB_ITEM => 0,
            self::NB_EXECUTIONS => 0,
            self::NB_FINISHED => 0,
        ]);

        return $result === 1;
    }

    /**
     * Increment one of the field
     * @param string $login
     * @param string $field
     */
    public function increment($login = '', $field = '')
    {
        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $field . " = " . $field . "+1 WHERE " . self::TEST_TAKER_LOGIN . "=?";
        $parameters = [$login];
        $this->getPersistence()
            ->exec($sql, $parameters);
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \common_Exception
     */
    public function get($login = '')
    {
        if (!$login) {
            throw new \common_Exception('TestTakerDeliveryLogService should have test taker login');
        }

        $sql = "SELECT * FROM " . self::TABLE_NAME . PHP_EOL;
        $sql .= "WHERE " . self::TEST_TAKER_LOGIN . "=? ";

        $parameters = [$login];

        $stmt = $this->getPersistence()->query($sql, $parameters);
        return current($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    private function getPersistence()
    {
        return \common_persistence_Manager::getPersistence($this->persistence);
    }
}
