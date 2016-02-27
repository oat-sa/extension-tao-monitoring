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

namespace oat\taoMonitoring\scripts\install;


use Doctrine\DBAL\Schema\SchemaException;
use oat\taoMonitoring\model\Delivery\implementation\RdsTestTakerDeliveryLogService;

/**
 * 
 * 
 * Class RegisterTestTakerLog
 * @package oat\taoMonitoring\scripts\install\Delivery
 */
class RegisterTestTakerDeliveryLog extends \common_ext_action_InstallAction
{
    
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';
        $persistence = \common_persistence_Manager::getPersistence($persistenceId);

        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $tableLog = $schema->createTable(RdsTestTakerDeliveryLogService::TABLE_NAME);
            $tableLog->addOption('engine', 'MyISAM');
            $tableLog->addColumn(RdsTestTakerDeliveryLogService::TEST_TAKER_LOGIN, "string", array("notnull" => true, "length" => 255));
            $tableLog->addColumn(RdsTestTakerDeliveryLogService::NB_ITEM, "integer");
            $tableLog->addColumn(RdsTestTakerDeliveryLogService::NB_EXECUTIONS, "integer");
            $tableLog->addColumn(RdsTestTakerDeliveryLogService::NB_FINISHED, "integer");

            $tableLog->setPrimaryKey(array(RdsTestTakerDeliveryLogService::TEST_TAKER_LOGIN));
            
        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema for Delivery\TestTakerLog already up to date.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        $this->registerService(
            RdsTestTakerDeliveryLogService::SERVICE_ID,
            new RdsTestTakerDeliveryLogService(array(RdsTestTakerDeliveryLogService::OPTION_PERSISTENCE => $persistenceId))
        );
        
        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery log for test taker'));
    }
}
