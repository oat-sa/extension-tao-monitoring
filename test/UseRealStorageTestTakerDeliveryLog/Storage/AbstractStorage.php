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


use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

class AbstractStorage extends TaoPhpUnitTestRunner
{
    protected $login = 'unittestTestTakerLogin';
    protected $login2 = 'unittestTestTakerLogin2';
    protected $login3 = 'unittestTestTakerLogin3';
    protected $login4 = 'unittestTestTakerLogin4';

    /**
     * @param $storage
     */
    protected function checkStorage(StorageInterface $storage)
    {
        $this->assertFalse($storage->getRow($this->login));

        $storage->createRow($this->login);
        $storage->incrementField($this->login, StorageInterface::NB_ITEM);

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 1,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0
        ], $storage->getRow($this->login));

        $storage->incrementField($this->login, StorageInterface::NB_ITEM);
        $storage->incrementField($this->login, StorageInterface::NB_ITEM);
        $storage->incrementField($this->login, StorageInterface::NB_ITEM);

        $storage->incrementField($this->login, StorageInterface::NB_EXECUTIONS);
        $storage->incrementField($this->login, StorageInterface::NB_EXECUTIONS);

        $storage->incrementField($this->login, StorageInterface::NB_FINISHED);

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 4,
            StorageInterface::NB_EXECUTIONS => 2,
            StorageInterface::NB_FINISHED => 1
        ], $storage->getRow($this->login));

        $forReplace = [
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 2,
            StorageInterface::NB_EXECUTIONS => 1,
            StorageInterface::NB_FINISHED => 5
        ];
        $storage->replace($forReplace);
        $this->assertEquals($forReplace, $storage->getRow($this->login));

        $storage->createRow($this->login2);

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login2,
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0
        ], $storage->getRow($this->login2));
        
        // check add data by rows (for updating data by deliveries)
        $newData = [[
                StorageInterface::TEST_TAKER_LOGIN => $this->login2,
                StorageInterface::NB_ITEM => 3,
                StorageInterface::NB_EXECUTIONS => 2,
                StorageInterface::NB_FINISHED => 200
            ],
            [
                StorageInterface::TEST_TAKER_LOGIN => $this->login,
                StorageInterface::NB_ITEM => 1030,
                StorageInterface::NB_EXECUTIONS => 59,
                StorageInterface::NB_FINISHED => 47
            ],
            [
                StorageInterface::TEST_TAKER_LOGIN => $this->login3,
                StorageInterface::NB_ITEM => 10,
                StorageInterface::NB_EXECUTIONS => 5,
                StorageInterface::NB_FINISHED => 4
            ],
            [
                StorageInterface::TEST_TAKER_LOGIN => $this->login4,
                StorageInterface::NB_ITEM => 0,
                StorageInterface::NB_EXECUTIONS => 0,
                StorageInterface::NB_FINISHED => 0
            ],
        ];
        $storage->flushArray($newData);

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 1030,
            StorageInterface::NB_EXECUTIONS => 59,
            StorageInterface::NB_FINISHED => 47
        ], $storage->getRow($this->login));
        
        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login2,
            StorageInterface::NB_ITEM => 3,
            StorageInterface::NB_EXECUTIONS => 2,
            StorageInterface::NB_FINISHED => 200
        ], $storage->getRow($this->login2));

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login3,
            StorageInterface::NB_ITEM => 10,
            StorageInterface::NB_EXECUTIONS => 5,
            StorageInterface::NB_FINISHED => 4
        ], $storage->getRow($this->login3));

        $this->assertEquals([
            StorageInterface::TEST_TAKER_LOGIN => $this->login4,
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0
        ], $storage->getRow($this->login4));
    }
}
