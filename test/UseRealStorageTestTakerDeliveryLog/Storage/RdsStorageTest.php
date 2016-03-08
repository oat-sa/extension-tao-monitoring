<?php


namespace oat\taoMonitoring\test\UseRealStorageTestTakerDeliveryLog\Storage;


use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;

class RdsStorageTest extends AbstractStorage
{

    public function tearDown()
    {
        $sql = "DELETE FROM " . RdsStorage::TABLE_NAME . " WHERE " . RdsStorage::TEST_TAKER_LOGIN . "=?"
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
