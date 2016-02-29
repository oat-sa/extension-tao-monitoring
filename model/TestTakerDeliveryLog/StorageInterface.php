<?php


namespace oat\taoMonitoring\model\TestTakerDeliveryLog;


interface StorageInterface
{
    /**
     * Create new log record
     * 
     * @param string $login
     * @return bool
     */
    public function create($login = '');

    /**
     * Get row
     * 
     * @param string $login
     * @return array
     */
    public function get($login = '');

    /**
     * Add new event to log
     * 
     * @param string $login
     * @param string $field
     * @return mixed
     */
    public function increment($login = '', $field = '');
}
