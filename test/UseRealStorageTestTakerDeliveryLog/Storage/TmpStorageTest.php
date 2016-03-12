<?php


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
