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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoMonitoring\scripts\tools;


use oat\oatbox\extension\script\ScriptAction;
use common_report_Report as Report;
use oat\taoMonitoring\model\InstantActionQueueLog\storage\InstantActionQueueLogRdsStorage;

/**
 * Class CreateInstantActionQueueRds
 * @package oat\taoMonitoring\scripts\tools
 */
class CreateInstantActionQueueRds extends ScriptAction
{
    protected function provideDescription()
    {
        return 'Create new storage for login queue monitoring';
    }

    protected function provideOptions()
    {
        return [
            'persistence' => [
                'prefix' => 'p',
                'longPrefix' => 'persistence',
                'required' => false,
                'description' => 'Persistence for the Storage (\'default\' is by default)'
            ],
        ];
    }

    protected function run()
    {
        $persistence = $this->hasOption('persistence') ? $this->getOption('persistence') : 'default';
        $storage = new InstantActionQueueLogRdsStorage( $persistence );
        $storage->createStorage();

        return new Report(
            Report::TYPE_SUCCESS,
            'Storage created!'
        );
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }
}
