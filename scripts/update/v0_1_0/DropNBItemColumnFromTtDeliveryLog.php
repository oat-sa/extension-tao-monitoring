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
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;

class DropNBItemColumnFromTtDeliveryLog implements Action
{
    const NB_ITEM = 'nb_item';

    public function __invoke($params)
    {
        $dbWrapper = \core_kernel_classes_DbWrapper::singleton();
        $tblName = RdsStorage::TABLE_NAME;
        $sql = 'ALTER TABLE "' . $tblName . '" DROP COLUMN ' . $dbWrapper->quoteIdentifier(self::NB_ITEM);

        try{
            $dbWrapper = \core_kernel_classes_DbWrapper::singleton();
            $dbWrapper->exec($sql);
        }
        catch (\PDOException $e){
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, "An error occured while removing column '".self::NB_ITEM."' from table '${tblName}': " . $e->getMessage());
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, "NB_ITEM was successfully removed");
    }
}
