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
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class RdsStorage implements StorageInterface
{

    const TABLE_NAME = 'monitoring_testtaker_deliveries';
    const OPTION_PERSISTENCE = 'persistence';

    /**
     * @var TestTakerDeliveryLogInterface
     */
    private $service;

    public function __construct(TestTakerDeliveryLogInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Create new test takers log
     * @param string $login
     * @return bool
     */
    public function createRow($login = '')
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
     * @return \common_persistence_SqlPersistence
     */
    private function getPersistence()
    {
        return \common_persistence_Manager::getPersistence($this->service->getOption(self::OPTION_PERSISTENCE));
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

        $sql = "SELECT * FROM " . self::TABLE_NAME . PHP_EOL;
        $sql .= "WHERE " . self::TEST_TAKER_LOGIN . "=? ";

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
            $tableLog->addColumn(self::NB_ITEM, "integer");
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
        $queries = [];
        foreach ($data as $row) {
            $queries[] = $this->getSqlUpdateOrCreate($row);
        }
        var_dump($queries);
    }

    private function getSqlUpdateOrCreate(array $data)
    {
        return "INSERT INTO " . self::TABLE_NAME
        . " (`"
        . self::TEST_TAKER_LOGIN . "`,`" . self::NB_ITEM . "`, `" . self::NB_EXECUTIONS . "`,`" . self::NB_FINISHED
        . "`) VALUES ('"
        . $data[self::TEST_TAKER_LOGIN] . "','"
        . $data[self::NB_ITEM] . "','"
        . $data[self::NB_EXECUTIONS] . "','"
        . $data[self::NB_FINISHED]
        . "') ON DUPLICATE KEY UPDATE "
        . "`" . self::NB_ITEM . "`=`". self::NB_ITEM . "`+" . $data[self::NB_ITEM] . ","
        . "`" . self::NB_EXECUTIONS . "`=`". self::NB_EXECUTIONS . "`+" . $data[self::NB_EXECUTIONS] . ","
        . "`" . self::NB_FINISHED . "`=`". self::NB_FINISHED . "`+" . $data[self::NB_FINISHED];
    }
}
