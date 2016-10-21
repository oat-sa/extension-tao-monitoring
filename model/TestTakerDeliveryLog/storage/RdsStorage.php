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


use Doctrine\DBAL\Schema\SchemaException;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

class RdsStorage implements StorageInterface
{

    const TABLE_NAME = 'monitoring_testtaker_deliveries';
    const OPTION_PERSISTENCE = 'persistence';

    /**
     * Persistence for DB
     * @var string
     */
    private $persistence;
    
    public function __construct($persistence = '')
    {
        $this->persistence = $persistence;
    }

    /**
     * Create new test takers log
     * @param string $login
     * @return bool
     */
    public function createRow($login = '')
    {
        $result = $this->getPersistence()->insert(RdsStorage::TABLE_NAME, [
            self::TEST_TAKER_LOGIN => $login,
            self::NB_EXECUTIONS => 0,
            self::NB_FINISHED => 0,
        ]);

        return $result === 1;
    }

    /**
     * @return \common_persistence_SqlPersistence
     */
    private function getPersistence()
    {
        return \common_persistence_Manager::getPersistence($this->persistence);
    }

    /**
     * Increment one of the field
     * @param string $login
     * @param string $field
     * @return bool
     */
    public function incrementField($login = '', $field = '')
    {
        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $field . " = " . $field . "+1 WHERE " . self::TEST_TAKER_LOGIN . "=?";
        $parameters = [$login];
        $r = $this->getPersistence()->exec($sql, $parameters);

        return $r === 1;
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \common_Exception
     */
    public function getRow($login = '')
    {
        if (!$login) {
            throw new \common_Exception('TestTakerDeliveryLogService should have test taker login');
        }

        $sql = "SELECT * FROM " . self::TABLE_NAME ." WHERE " . self::TEST_TAKER_LOGIN . "=? ";

        $parameters = [$login];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return current($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    /**
     * @return bool
     */
    public function createStorage()
    {
        $persistence = $this->getPersistence();
        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $tableLog = $schema->createTable(self::TABLE_NAME);
            $tableLog->addOption('engine', 'MyISAM');
            $tableLog->addColumn(self::TEST_TAKER_LOGIN, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::NB_EXECUTIONS, "integer");
            $tableLog->addColumn(self::NB_FINISHED, "integer");

            $tableLog->setPrimaryKey(array(self::TEST_TAKER_LOGIN));

        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema for Delivery\TestTakerLog already up to date.');
            return false;
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        return true;
    }

    public function dropStorage()
    {
        $persistence = $this->getPersistence();

        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $schema->dropTable(self::TABLE_NAME);
        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema for Delivery\TestTakerLog can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }

    public function flushArray(array $data)
    {
        foreach ($data as $row) {
            $this->replace($row);
        }
    }

    public function countAllData()
    {
        $sql = "SELECT COUNT(" . self::TEST_TAKER_LOGIN . ") FROM " . self::TABLE_NAME;
        $stmt = $this->getPersistence()->query($sql);
        return current(current($stmt->fetchAll(\PDO::FETCH_ASSOC)));
    }
    
    public function replace(array $data)
    {

        $row = $this->getRow($data[self::TEST_TAKER_LOGIN]);

        if ($row && count($row)) {
            $sql = "UPDATE " . self::TABLE_NAME . " SET "
                . self::NB_EXECUTIONS . " = ?, "
                . self::NB_FINISHED . " = ? "
                . "WHERE " . self::TEST_TAKER_LOGIN . "= ?"
            ;

            $parameters = [$data[self::NB_EXECUTIONS], $data[self::NB_FINISHED], $data[self::TEST_TAKER_LOGIN]];
            $res = $this->getPersistence()->query($sql, $parameters);
        } else {
            $res = $this->getPersistence()->insert(self::TABLE_NAME, [
                self::TEST_TAKER_LOGIN => $data[self::TEST_TAKER_LOGIN],
                self::NB_EXECUTIONS => $data[self::NB_EXECUTIONS],
                self::NB_FINISHED => $data[self::NB_FINISHED],
            ]);
        }

        return $res;
    }
    
    public function getSlice($page = 0, $inPage = 500)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " ORDER BY " . self::TEST_TAKER_LOGIN . " LIMIT ? OFFSET ?";

        $parameters = [$inPage, $inPage*$page];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
