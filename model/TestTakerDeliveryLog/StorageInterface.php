<?php


namespace oat\taoMonitoring\model\TestTakerDeliveryLog;


use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

interface StorageInterface extends DataAggregatorInterface
{
    /** Fields */
    // test taker login
    const TEST_TAKER_LOGIN = 'test_taker';
    // events
    const NB_ITEM = 'nb_item';
    const NB_EXECUTIONS = 'nb_executions';
    const NB_FINISHED = 'nb_finished';

    /**
     * StorageInterface constructor.
     * @param TestTakerDeliveryLogInterface $service
     */
    public function __construct(TestTakerDeliveryLogInterface $service);
    
    /**
     * Create new log record
     * 
     * @param string $login
     * @return bool
     */
    public function createRow($login = '');

    /**
     * Get row
     * 
     * @param string $login
     * @return array
     */
    public function getRow($login = '');

    /**
     * Add new event to log
     * 
     * @param string $login
     * @param string $field
     * @return bool
     */
    public function incrementField($login = '', $field = '');

    /**
     * Create storage
     * @return bool
     */
    public function createStorage();
    
    /**
     * Destroy storage
     * @return bool
     */
    public function dropStorage();

    /**
     * Add Or Create data in storage
     * # If test taker exists - add data to storage
     *  [
     *    'test_taker' => int,
     *    'nb_item' => int,
     *    'nb_executions' => int,
     *    'nb_finished' => int,
     *  ]
     * @param array $data
     */
    public function flushArray(array $data);

    /**
     * Replace or Create data in storage
     * @param array $data
     * @return mixed
     */
    public function replace(array $data);
}
