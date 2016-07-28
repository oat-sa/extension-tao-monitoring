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

namespace oat\taoMonitoring\model\TestTakerDeliveryActivityLog\storage;


use Doctrine\DBAL\Schema\SchemaException;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\StorageInterface;

class RdsStorage implements StorageInterface
{

    const TABLE_NAME = 'monitoring_testtaker_delivery_activity';
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
     * Write event to db
     * @param string $testTaker
     * @param string $delivery
     * @param string $deliveryExecution
     * @param string $event
     * @return bool
     */
    public function event($testTaker = '', $delivery = '', $deliveryExecution = '', $event = '')
    {
        $result = $this->getPersistence()->insert(self::TABLE_NAME, [
            self::TEST_TAKER => $testTaker,
            self::DELIVERY => $delivery,
            self::DELIVERY_EXECUTION => $deliveryExecution,
            self::EVENT => $event,
            self::TIME => date('Y-m-d H:i:s')
        ]);

        $id = $this->getPersistence()->lastInsertId(self::TABLE_NAME);
        if ( ($id % 1000) == 0 ) {
            //every 1000 inserts try to delete obsolete data from log
            $this->cleanStorage();
        }

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

            $tableLog->addColumn(self::ID, "integer", array("notnull" => true, "autoincrement" => true));
            $tableLog->addColumn(self::TEST_TAKER, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::EVENT, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::DELIVERY, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::DELIVERY_EXECUTION, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::TIME, "datetime", array("notnull" => true));

            $tableLog->setPrimaryKey(array(self::ID));
            $tableLog->addIndex([self::TEST_TAKER], 'idx_test_taker');
            $tableLog->addIndex([self::TIME], 'idx_time');
            $tableLog->addIndex([self::DELIVERY], 'idx_delivery');
            $tableLog->addIndex([self::DELIVERY_EXECUTION], 'idx_delivery_execution');

        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema for TestTakerDeliveryActivityLog already up to date.');
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
            \common_Logger::i('Database Schema for TestTakerDeliveryActivityLog can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }

    private function cleanStorage($dateRange = '-1 week')
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::TIME . " <= ?";

        $parameters = [date('Y-m-d H:i:s', strtotime($dateRange))];
        $this->getPersistence()->query($sql, $parameters);

        return true;
    }

    public function getLastActivity($deliveryUri = '', $dateRange = '-1 day', $onlyActive = false)
    {
        
        $excludedEvents = [];
        if ($onlyActive) {
            $excludedEvents = ['deliveryExecutionFinish'];
        }
        
        $sql = "SELECT COUNT(DISTINCT " . self::TEST_TAKER . ") AS count, " . self::TIME . " AS hour FROM " . self::TABLE_NAME
            . " WHERE " . self::DELIVERY . " = ? AND " . self::TIME . " >= ? "
            
            . (count($excludedEvents) 
                ? "AND " . self::DELIVERY_EXECUTION . " NOT IN ( SELECT " . self::DELIVERY_EXECUTION . " FROM " . self::TABLE_NAME
                    . " WHERE " . implode(' OR ', array_fill(0, count($excludedEvents), 'event = ?'))
                    . " AND " . self::TIME . " >= ?"
                . ")"
                
                : '') 
            
            . " GROUP BY HOUR(" . self::TIME . "), DAY(" . self::TIME . ") ORDER BY " . self::TIME;
        
        $time = date('Y-m-d H:i:s', strtotime($dateRange));
        $parameters = [$deliveryUri, $time];
        
        if (count($excludedEvents)) {
            $parameters = array_merge($parameters, $excludedEvents);
            $parameters[] = $time;
        }
        
        $stmt = $this->getPersistence()->query($sql, $parameters);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
