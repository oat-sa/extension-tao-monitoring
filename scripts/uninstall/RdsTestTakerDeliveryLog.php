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

namespace oat\taoMonitoring\scripts\uninstall\Delivery;


use Doctrine\DBAL\Schema\SchemaException;
use oat\taoMonitoring\model\implementation\RdsTestTakerDeliveryLogService;

class TestTakerLog extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $persistenceId = count($params) > 0 ? reset($params) : 'default';
        $persistence = \common_persistence_Manager::getPersistence($persistenceId);

        $schemaManager = $persistence->getDriver()->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $fromSchema = clone $schema;

        try {
            $schema->dropTable(RdsTestTakerDeliveryLogService::TABLE_NAME);
        } catch(SchemaException $e) {
            \common_Logger::i('Database Schema for Delivery\TestTakerLog can\'t be dropped.');
        }

        $queries = $persistence->getPlatform()->getMigrateSchemaSql($fromSchema, $schema);
        foreach ($queries as $query) {
            $persistence->exec($query);
        }

        if ($this->getServiceManager()->has(RdsTestTakerDeliveryLogService::SERVICE_ID)) {
            $this->registerService(RdsTestTakerDeliveryLogService::SERVICE_ID, null);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, __('Registered delivery log for test taker'));
    }
}
