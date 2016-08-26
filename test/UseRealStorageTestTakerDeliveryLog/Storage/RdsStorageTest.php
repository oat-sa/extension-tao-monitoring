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

namespace oat\taoMonitoring\test\UseRealStorageTestTakerDeliveryLog\Storage;


use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;

class RdsStorageTest extends AbstractStorage
{

    public function tearDown()
    {
        $sql = "DELETE FROM " . RdsStorage::TABLE_NAME
            . " WHERE " . RdsStorage::TEST_TAKER_LOGIN . "=?"
            . " OR " . RdsStorage::TEST_TAKER_LOGIN . "=?"
            . " OR " . RdsStorage::TEST_TAKER_LOGIN . "=?"
            . " OR " . RdsStorage::TEST_TAKER_LOGIN . "=?";
        
        $parameters = [$this->login, $this->login2, $this->login3, $this->login4];
        $persistence = \common_persistence_Manager::getPersistence('default');
        $persistence->exec($sql, $parameters);
    }

    public function testStorage()
    {
        $storage = new RdsStorage('default');
        $storage->createStorage();
        
        $this->checkStorage($storage);
    }
}
