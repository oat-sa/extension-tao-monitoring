<?php


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
        StorageInterface::NB_FINISHED => 0], $storage->getRow($this->login));

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
        StorageInterface::NB_FINISHED => 1], $storage->getRow($this->login));

        $this->assertEquals(1, $storage->countAllData());

        $forReplace = [
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 2,
            StorageInterface::NB_EXECUTIONS => 1,
            StorageInterface::NB_FINISHED => 5
        ];
        $storage->replace($forReplace);
        $this->assertEquals($forReplace, $storage->getRow($this->login));

        $storage->createRow($this->login2);
        $this->assertEquals(2, $storage->countAllData());

        $this->assertEquals([[
        StorageInterface::TEST_TAKER_LOGIN => $this->login2,
        StorageInterface::NB_ITEM => 0,
        StorageInterface::NB_EXECUTIONS => 0,
        StorageInterface::NB_FINISHED => 0]], $storage->getSlice(1, 1));
        
        // check add data by rows (for updating data by deliveries)
        $newData = [
            [
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

        $this->assertEquals([[
            StorageInterface::TEST_TAKER_LOGIN => $this->login,
            StorageInterface::NB_ITEM => 1032,
            StorageInterface::NB_EXECUTIONS => 60,
            StorageInterface::NB_FINISHED => 52,
        ],[
            StorageInterface::TEST_TAKER_LOGIN => $this->login2,
            StorageInterface::NB_ITEM => 3,
            StorageInterface::NB_EXECUTIONS => 2,
            StorageInterface::NB_FINISHED => 200,
        ],[
            StorageInterface::TEST_TAKER_LOGIN => $this->login3,
            StorageInterface::NB_ITEM => 10,
            StorageInterface::NB_EXECUTIONS => 5,
            StorageInterface::NB_FINISHED => 4,
        ],[
            StorageInterface::TEST_TAKER_LOGIN => $this->login4,
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0,
        ]], $storage->getSlice());
    }
}
