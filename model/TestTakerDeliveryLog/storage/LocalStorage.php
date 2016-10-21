<?php


namespace oat\taoMonitoring\model\TestTakerDeliveryLog\storage;


use oat\taoMonitoring\model\TestTakerDeliveryLog\StorageInterface;

class LocalStorage implements StorageInterface
{
    /**
     * Storage for unit test
     * @var null|array
     */
    protected $storage = null;

    public function __construct($path = '')
    {
    }

    public function createStorage()
    {
        $this->storage = [];
    }

    public function dropStorage()
    {
        $this->storage = null;
    }

    public function createRow($login = '')
    {
        if ($this->loginExists($login)) {
            return true;
        }

        array_push($this->storage, [
            StorageInterface::TEST_TAKER_LOGIN => $login,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0,
        ]);

        return true;
    }

    private function loginExists($login = '')
    {
        if ($this->storage) {
            foreach ($this->storage as $row) {
                if ($row[StorageInterface::TEST_TAKER_LOGIN] === $login) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function getRow($login = '')
    {
        if (isset($this->storage)) {
            foreach ($this->storage as $row) {
                if ($row[StorageInterface::TEST_TAKER_LOGIN] === $login) {
                    return $row;
                }
            }
        }
        return false;
    }

    public function incrementField($login = '', $field = '')
    {
        foreach ($this->storage as $key => $row) {
            if ($row[StorageInterface::TEST_TAKER_LOGIN] === $login) {
                if (!isset($this->storage[$key][$field])) {
                    throw new \common_Exception('Field "' . $field . '" is not exists"');
                }
                $this->storage[$key][$field]++;
                return true;
            }
        }
        return false;
    }

    public function flushArray(array $data)
    {
        foreach ($data as $dRow) {
            foreach ($this->storage as $sKey => $sRow) {

                if ($sRow[StorageInterface::TEST_TAKER_LOGIN] == $dRow[StorageInterface::TEST_TAKER_LOGIN]) {
                    foreach ([StorageInterface::NB_EXECUTIONS, StorageInterface::NB_FINISHED] as $item) {
                        $this->storage[$sKey][$item] += $dRow[$item];
                    }
                    continue 2;
                }
            }
            //if still here - new record
            array_push($this->storage, $dRow);
        }
    }

    public function countAllData()
    {
        return count($this->storage);
    }

    public function getSlice($page = 0, $inPage = 500)
    {
        return array_slice($this->storage, $page * $inPage, $inPage);
    }

    public function replace(array $data)
    {
        foreach ($this->storage as $key => $row) {
            if ($row[StorageInterface::TEST_TAKER_LOGIN] === $data[StorageInterface::TEST_TAKER_LOGIN]) {
                $this->storage[$key] = $data;
                return;
            }
        }
        
        // if not replaced, just add new row
        array_push($this->storage, $data);
    }
}
