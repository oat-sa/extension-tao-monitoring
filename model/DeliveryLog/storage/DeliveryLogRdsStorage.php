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

namespace oat\taoMonitoring\model\DeliveryLog\storage;


use Doctrine\DBAL\Schema\SchemaException;
use oat\taoMonitoring\model\DeliveryLog\DeliveryLogStorageInterface;

class DeliveryLogRdsStorage implements DeliveryLogStorageInterface
{
    const TABLE_NAME = 'monitoring_delivery';
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
     * Create new delivery log
     * @param string $deliveryUri
     * @return bool
     */
    public function createRow($deliveryUri = '')
    {
        $result = $this->getPersistence()->insert(self::TABLE_NAME, [
            self::DELIVERY => $deliveryUri,
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
     * @param string $deliveryUri
     * @param string $field
     * @return bool
     */
    private function incrementField($deliveryUri = '', $field = '')
    {
        if (!$this->getRow($deliveryUri)) {
            $this->createRow($deliveryUri);
        }

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $field . " = " . $field . "+1 WHERE " . self::DELIVERY . "=?";
        $parameters = [$deliveryUri];
        $r = $this->getPersistence()->exec($sql, $parameters);

        return $r === 1;
    }

    /**
     * Increment executions
     * @param string $deliveryUri
     * @return bool
     */
    public function addExecution($deliveryUri = '')
    {
        return $this->incrementField($deliveryUri, DeliveryLogStorageInterface::NB_EXECUTIONS);
    }

    /**
     * Increment finished
     * @param string $deliveryUri
     * @return bool
     */
    public function addFinishedExecution($deliveryUri = '')
    {
        return $this->incrementField($deliveryUri, DeliveryLogStorageInterface::NB_FINISHED);
    }

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
            $tableLog->addColumn(self::DELIVERY, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(self::NB_EXECUTIONS, "integer", array("unsigned" => true));
            $tableLog->addColumn(self::NB_FINISHED, "integer", array("unsigned" => true));

            $tableLog->setPrimaryKey(array(self::DELIVERY));

        } catch (SchemaException $e) {
            \common_Logger::i('Database Schema for DeliveryLog already up to date.');
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
            \common_Logger::i('Database Schema for DeliveryLog can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }
    }
}
