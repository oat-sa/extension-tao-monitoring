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
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLogInterface;

class Service extends ConfigurableService
    implements TestTakerDeliveryLogInterface
{

    /**
     * @var StorageInterface
     */
    private $storage;

    public function updateTestTaker(DataAggregatorInterface $aggregator)
    {

        // recount statistics for test taker
        $statistics = current($aggregator->getSlice());
        $this->storage()->replace($statistics);

    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    private function storage()
    {
        if (!isset($this->storage)) {
            $this->storage = $this->hasOption(TmpStorage::OPTION_TMP_FILE)
                ? new TmpStorage($this->getOption(TmpStorage::OPTION_TMP_FILE))
                : new RdsStorage($this->getOption(RdsStorage::OPTION_PERSISTENCE));
        }

        return $this->storage;
    }
}
