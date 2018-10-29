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

namespace oat\taoMonitoring\scripts\update\v0_1_0;


use oat\oatbox\action\Action;


// todo delete that redundant action in the new PR
class DropTestTakerDeliveryLogTable implements Action
{
    const TABLE_NAME = 'monitoring_testtaker_deliveries';

    public function __invoke($params)
    {
        $sql = 'DROP TABLE "' . self::TABLE_NAME . '"';
        try{
            $dbWrapper = \core_kernel_classes_DbWrapper::singleton();
            $dbWrapper->exec($sql);
        }
        catch (\PDOException $e){
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, "An error occured while removing table '".self::TABLE_NAME."' " . $e->getMessage());
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, self::TABLE_NAME . " was successfully removed");
    }
}
