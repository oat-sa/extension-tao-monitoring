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
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\storage\RdsStorage;


/**
 * Tests for real database
 *
 * Class RdsStorageTestTest
 * @package oat\taoMonitoring\test\TestTakerDeliveryActivityLog
 */
class RdsStorageTestTest extends TaoPhpUnitTestRunner
{
    private $tt1 = '#1';
    private $tt2 = '#2';
    private $tt3 = '#3';

    /**
     * @var RdsStorage
     */
    private $storage;

    public function tearDown()
    {
        parent::tearDown();

        $sql = "DELETE FROM " . RdsStorage::TABLE_NAME . " WHERE " . RdsStorage::TEST_TAKER . "= ?"
            . " OR " . RdsStorage::TEST_TAKER . "= ?"
            . " OR " . RdsStorage::TEST_TAKER . "= ?";

        $parameters = [$this->tt1, $this->tt2, $this->tt3];
        $persistence = \common_persistence_Manager::getPersistence('default');
        $persistence->exec($sql, $parameters);
    }

    public function setUp()
    {
        // for tests if not exists
        $this->storage = new RdsStorage('default');
        $this->storage->createStorage();
    }

    public function testEvent()
    {
        $this->assertEquals([], $this->storage->getLastActivity('delivery'));

        // add new event
        $this->storage->event($this->tt1, 'delivery', 'deliveryExecution', 'event');
        $this->assertEquals(1, $this->storage->getLastActivity('delivery')[0]['count']);
    }

    public function testSameEvent()
    {
        // add same event
        $this->storage->event($this->tt1, 'delivery', 'deliveryExecution', 'event');
        $this->storage->event($this->tt1, 'delivery', 'deliveryExecution', 'event');
        $this->assertEquals(1, $this->storage->getLastActivity('delivery')[0]['count']);
    }

    public function testOtherEvent()
    {
        // other test takers
        $this->storage->event($this->tt2, 'delivery', 'deliveryExecution', 'event');
        $this->storage->event($this->tt1, 'delivery', 'deliveryExecution', 'event');
        $this->storage->event($this->tt3, 'delivery', 'deliveryExecution', 'event');
        $this->assertEquals(3, $this->storage->getLastActivity('delivery')[0]['count']);
    }

    public function testConnectedUsers()
    {
        $this->storage->event($this->tt2, 'delivery', 'deliveryExecution1', 'event');
        $this->storage->event($this->tt1, 'delivery', 'deliveryExecution2', 'event');
        $this->storage->event($this->tt3, 'delivery', 'deliveryExecution3', 'event');
        $this->assertEquals(3, $this->storage->getLastActivity('delivery', '-10 minutes', true)[0]['count']);

        // finish one delivery
        $this->storage->event($this->tt3, 'delivery', 'deliveryExecution3', 'deliveryExecutionFinish');
        // in activity it should be
        $this->assertEquals(3, $this->storage->getLastActivity('delivery')[0]['count']);
        // in list of the active users it's don't displayed
        $this->assertEquals(2, $this->storage->getLastActivity('delivery', '-10 minutes', true)[0]['count']);
    }

    /**
     * $id % 1000 should clear the log
     */
    public function testCleaner()
    {
        $this->assertFalse($this->inArray(9999));

        $persistence = \common_persistence_Manager::getPersistence('default');
        $persistence->insert(RdsStorage::TABLE_NAME, [
            RdsStorage::ID => 9999,
            RdsStorage::TEST_TAKER => $this->tt1,
            RdsStorage::EVENT => 'test',
            RdsStorage::DELIVERY => 'dlv',
            RdsStorage::DELIVERY_EXECUTION => 'dlv_e',
            RdsStorage::TIME => date('Y-m-d H:i:s', strtotime('-1 month'))
        ]);


        $this->assertTrue($this->inArray(9999));
        
        $class = new \ReflectionClass(RdsStorage::class);
        $cleanMethod = $class->getMethod('cleanStorage');
        $cleanMethod->setAccessible(true);
        $cleanMethod->invokeArgs($this->storage, []);

        $this->assertFalse($this->inArray(9999));
    }

    private function inArray($id = 0)
    {
        $sql = "SELECT * FROM " . RdsStorage::TABLE_NAME . " WHERE " . RdsStorage::ID . " = ?";
        $persistence = \common_persistence_Manager::getPersistence('default');
        $stmt = $persistence->query($sql, [$id]);
        return count($stmt->fetchAll(\PDO::FETCH_ASSOC)) > 0;
    }
}
