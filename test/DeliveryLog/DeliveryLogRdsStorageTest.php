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

namespace oat\taoMonitoring\test\TestTakerDeliveryActivityLog;


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\DeliveryLog\storage\DeliveryLogRdsStorage as RdsStorage;

/**
 * Tests for real database
 *
 * Class RdsStorageTestTest
 * @package oat\taoMonitoring\test\TestTakerDeliveryActivityLog
 */
class DeliveryLogRdsStorageTest extends TaoPhpUnitTestRunner
{
    private $delivery1 = '#1';
    private $delivery2 = '#2';
    private $delivery3 = '#3';

    /**
     * @var RdsStorage
     */
    private $storage;

    public function tearDown()
    {
        parent::tearDown();

        $sql = "DELETE FROM " . RdsStorage::TABLE_NAME
            . " WHERE " . RdsStorage::DELIVERY . "= ?"
            . " OR " . RdsStorage::DELIVERY . "= ?"
            . " OR " . RdsStorage::DELIVERY . "= ?";

        $parameters = [$this->delivery1, $this->delivery2, $this->delivery3];
        $persistence = \common_persistence_Manager::getPersistence('default');
        $persistence->exec($sql, $parameters);
    }

    public function testStorage()
    {
        // for tests if not exists
        $this->storage = new RdsStorage('default');
        $this->storage->createStorage();

        $this->assertFalse($this->storage->getRow($this->delivery1));

        $this->storage->createRow($this->delivery1);
        $this->assertEquals([
            RdsStorage::DELIVERY => $this->delivery1,
            RdsStorage::NB_EXECUTIONS => 0,
            RdsStorage::NB_FINISHED => 0
        ], $this->storage->getRow($this->delivery1));

        $this->storage->addExecution($this->delivery1);
        $this->storage->addExecution($this->delivery1);
        $this->storage->addFinishedExecution($this->delivery1);

        $this->assertEquals([
            RdsStorage::DELIVERY => $this->delivery1,
            RdsStorage::NB_EXECUTIONS => 2,
            RdsStorage::NB_FINISHED => 1
        ], $this->storage->getRow($this->delivery1));

        $this->storage->addExecution($this->delivery2);

        // without changes
        $this->assertEquals([
            RdsStorage::DELIVERY => $this->delivery1,
            RdsStorage::NB_EXECUTIONS => 2,
            RdsStorage::NB_FINISHED => 1
        ], $this->storage->getRow($this->delivery1));

        // was created new row
        $this->assertEquals([
            RdsStorage::DELIVERY => $this->delivery2,
            RdsStorage::NB_EXECUTIONS => 1,
            RdsStorage::NB_FINISHED => 0
        ], $this->storage->getRow($this->delivery2));
    }
}
