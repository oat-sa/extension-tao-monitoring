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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog\storage;


use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

/**
 * Class TmpStorage
 * 
 * // At the moment update doesn't work "on fly" (just for initialization)
 * 
 * ToDo Create tmp file, where will be saved sql queries
 * todo In update script add time of the update starting and check, that all executions less then update time
 * 
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog\storage
 */
class TmpStorage implements StorageInterface
{

    const TMP_STORAGE_FILE_PATH = 'tmpFile';
    
    /**
     * @var TestTakerDeliveryLogInterface
     */
    private $service;

    public function __construct(TestTakerDeliveryLogInterface $service)
    {
        $this->service = $service;
    }

    public function createStorage()
    {
        // create file
    }
    
    public function dropStorage()
    {
        //drop file
    }

    private function getPath()
    {
        return $this->service->getOption(self::TMP_STORAGE_FILE_PATH);
    }
    
    public function createRow($login = '')
    {
    }
    
    public function countAllData()
    {
        // TODO: Implement countAllData() method.
    }
    
    public function flushArray(array $data)
    {
        // TODO: Implement flushArray() method.
    }
    
    public function getRow($login = '')
    {
        // TODO: Implement getRow() method.
    }
    
    public function getSlice($page = 0, $inPage = 500)
    {
        // TODO: Implement getSlice() method.
    }
    
    public function incrementField($login = '', $field = '')
    {
        // TODO: Implement incrementField() method.
    }
    
    public function replace(array $data)
    {
        // TODO: Implement replace() method.
    }
}
