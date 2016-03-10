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

class TmpStorage extends LocalStorage
{

    const OPTION_TMP_FILE = 'tmpFile';

    /**
     * Path to file
     * @var string
     */
    private $path;

    public function __construct($path = '')
    {
        $this->path = $path;

        if (!empty($this->path)) {
            if (!is_writable($this->path)) {
                throw new \common_Exception("Storage file should be writable");
            } else {
                $this->readStorage();
            }
        }
    }

    /**
     * Return filePath
     */
    public function createStorage()
    {
        if (!file_exists($this->path)) {
            parent::createStorage();
            $this->path = tempnam(sys_get_temp_dir(), TmpStorage::OPTION_TMP_FILE);
        }

        return $this->path;
    }

    public function dropStorage()
    {
        parent::dropStorage();
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function createRow($login = '')
    {
        parent::createRow($login);
        $this->saveStorageInFile();
    }

    private function readStorage()
    {
        $aLogin = file($this->path);
        parent::createStorage();
        foreach ($aLogin as $login) {
            $this->createRow(trim($login));
        }
    }
    
    private function saveStorageInFile()
    {
        $login = [];
        foreach ($this->storage as $key => $row) {
            $login[] = $row[StorageInterface::TEST_TAKER_LOGIN];
        }

        if (empty($this->path) || !is_writable($this->path)) {
            throw new \common_Exception('Storage path not found: ' . $this->path);
        } else {
            $login = array_unique($login);
            file_put_contents($this->path, implode("\n", $login));
        }
    }

    public function incrementField($login = '', $field = '')
    {
        parent::incrementField($login, $field);
        $this->saveStorageInFile();
    }

    public function replace(array $data)
    {
        parent::replace($data);
        $this->saveStorageInFile();
    }
    
    public function flushArray(array $data)
    {
        parent::flushArray($data);
        $this->saveStorageInFile();
    }
    
    public function countAllData()
    {
        $this->readStorage();
        return parent::countAllData();
    }
    
    public function getRow($login = '')
    {
        $this->readStorage();
        return parent::getRow($login);
    }
    
    public function getSlice($page = 0, $inPage = 500)
    {
        $this->readStorage();
        return parent::getSlice($page, $inPage);
    }
}
