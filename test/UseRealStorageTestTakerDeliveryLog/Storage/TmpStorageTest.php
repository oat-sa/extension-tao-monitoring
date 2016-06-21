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


use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

class TmpStorageTest extends AbstractStorage
{

    public function testStorage()
    {
        $storage = new TmpStorage();
        $path = $storage->createStorage();
        
        $this->assertFileExists($path);

        $this->assertFalse($storage->getRow($this->login));

        $storage->createRow($this->login);
        $storage->incrementField($this->login, StorageInterface::NB_ITEM);

        // check file storage
        $storage2 = new TmpStorage($path);
        $this->assertEquals(1, $storage2->countAllData());

        $this->assertEquals([[
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0]], $storage2->getSlice());
        
        $storage->dropStorage();
        $this->assertFileNotExists($path);
    }
}
