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
     * @param string $event
     * @return bool
     */
    public function event($testTaker = '', $delivery = '', $event = '')
    {
        $result = $this->getPersistence()->insert(self::TABLE_NAME, [
            self::TEST_TAKER => $testTaker,
            self::DELIVERY => $delivery,
            self::EVENT => $event,
            self::TIME => time()
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
            $tableLog->addColumn(self::TIME, "integer", array("notnull" => true));

            $tableLog->setPrimaryKey(array(self::ID));
            $tableLog->addIndex([self::TEST_TAKER], 'idx_test_taker');
            $tableLog->addIndex([self::TIME], 'idx_time');
            $tableLog->addIndex([self::DELIVERY], 'idx_delivery');

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

    public function cleanStorage($date_range = '-1 week')
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE "
            . self::TIME . " <= ?";

        $parameters = [strtotime($date_range)];
        $stmt = $this->getPersistence()->query($sql, $parameters);
        if (!$stmt || !($res = $stmt->rowCount())) {
        }

        return true;
    }
}
