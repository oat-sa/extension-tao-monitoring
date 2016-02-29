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

namespace oat\taoMonitoring\model\TestTakerDeliveryLog;


use oat\oatbox\service\ConfigurableService;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Storage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class Service extends ConfigurableService
    implements TestTakerDeliveryLogInterface
{

    const OPTION_PERSISTENCE = 'persistence';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param string $testTakerLogin
     * @param string $nb_event
     * @throws \common_Exception
     * @return bool
     */
    public function logEvent($testTakerLogin = '', $nb_event = '')
    {

        if (!isset($testTakerLogin) || empty($testTakerLogin)) {
            throw new \common_Exception('TestTakerDeliveryLogService should have test taker login');
        }

        $testTakerLog = $this->storage()->get($testTakerLogin);

        if (!$testTakerLog || !count($testTakerLog)) {
            //create record
            $this->storage()->create($testTakerLogin);
            $testTakerLog = $this->storage()->get($testTakerLogin);
        }

        if (!isset($testTakerLog[$nb_event])) {
            throw new \common_Exception('TestTakerDeliveryLogService has incorrect $nb_event');
        }

        $testTakerLog[$nb_event]++;

        $this->storage()->increment($testTakerLogin, $nb_event);

        return true;
    }

    private function storage()
    {
        if (!isset($this->storage)) {
            $this->storage = $this->isLocked() 
                ? new TmpStorage($this->getOption('tmpPath')) 
                : new RdsStorage($this->getOptions(self::OPTION_PERSISTENCE));
        }

        return $this->storage;
    }

    public function isLocked()
    {
        return $this->hasOption('locked');
    }

    public function upgrade()
    {
        $this->lock();

        $this->generateStats();
        $this->fillTmpToDb();

        $this->unlock();
    }

    /**
     * For upgrade we need switch one storage to another
     */
    private function lock()
    {
        if ($this->isLocked()) {
            throw new \common_Exception('Upgrade is running');
        }

        $this->setOption('locked', true);
    }

    private function unlock()
    {
        $options = $this->getOptions();
        unlink($options['tmpPath']);
        unset($options['locked'], $options['tmpPath']);
        $this->setOptions($options);
    }
}
