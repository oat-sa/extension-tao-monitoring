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

/**
 * Class TmpStorage
 * @package oat\taoMonitoring\model\TestTakerDeliveryLog\storage
 */
class TmpStorage implements StorageInterface
{

    /**
     * @var string Tmp storage
     */
    private $tmpFilePath;
    
    public function __construct($filePath = '')
    {
        if (!$filePath || !file_exists($filePath)) {
            throw new \common_exception_Error('Tmp file could not be found');
        }
        
        $this->tmpFilePath = $filePath;
    }

    public function create($login = '')
    {
        
    }

    public function increment($login = '', $field = '')
    {
        if (!is_writable($this->tmpFilePath)) {
            throw new \common_exception_Error('Tmp file is not writable');
        }
        
        
    }
    
    public function get($login = '')
    {
        

        $sql = "SELECT * FROM " . self::TABLE_NAME . PHP_EOL;
        $sql .= "WHERE " . self::TEST_TAKER_LOGIN . "=? ";

        $parameters = [$login];

        $stmt = $this->getPersistence()->query($sql, $parameters);
        return current($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}
