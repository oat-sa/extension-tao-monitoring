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

        $this->checkStorage($storage);
        
        // check file storage
        $storage2 = new TmpStorage($path);
        $this->assertEquals(4, $storage2->countAllData());

        $this->assertEquals([[
            StorageInterface::TEST_TAKER_LOGIN => $this->login2,
            StorageInterface::NB_ITEM => 0,
            StorageInterface::NB_EXECUTIONS => 0,
            StorageInterface::NB_FINISHED => 0]], $storage2->getSlice(1,1));        
        
        $storage->dropStorage();
        $this->assertFileNotExists($path);
    }
}
