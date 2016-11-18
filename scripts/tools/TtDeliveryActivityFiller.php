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

namespace oat\taoMonitoring\scripts\tools;

use oat\oatbox\action\Action;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLog\TestTakerDeliveryActivityLogService;


/**
 * Load fake data into the storage, for easiest testing and developing
 *
 * Class MonitoringFiller
 * @package oat\taoMonitoring\scripts\tools
 */
class TtDeliveryActivityFiller implements Action
{

    public function __invoke($params)
    {
        $dryrun = in_array('dryrun', $params) || in_array('--dryrun', $params);
        $report = new \common_report_Report(\common_report_Report::TYPE_INFO, 'Creating fake data for the monitoring of the testTakerDeliveryActivity');

        if (!DEBUG_MODE) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'This tool allowed only in DEBUG mode because this can destroy data');
        }

        $count = $this->getCountRows();
        if ($dryrun) {
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'DryRun mode (data won\'t be written)'));
            $report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, "Also you can use extra parameters:\n\t--limit [20000] - limit of the records\n\t--days [2] - days to be filled with actions before & with today\n\t--free-hours - [10,15] excluded hours when nothing happens (false as default)\n\t--test-takers - [1000] count of the tt\n\t--delivery-uri - uri of the delivery\n\t--show-tt - show added testtakers (only for dry run mode)"));

            $report->add(new \common_report_Report(\common_report_Report::TYPE_ERROR, 'Will be deleted ' . $count . ' row' . ($count ? 's' : '')));
        } else {
            $this->deleteAllRows();
            $report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, 'Deleted ' . $count . ' row' . ($count ? 's' : '')));
        }

        $config = [
            '--limit' => 20000,
            '--days' => 2,
            '--delivery-uri' => false,
            '--test-taker' => 1000,
            '--free-hours' => false,
            '--show-tt' => false
        ];
        foreach ($params as $param) {
            foreach ($config as $key => $row) {
                if (strpos($param, $key . '=') !== false ) {
                    $config[$key] = str_replace($key . '=', '', $param);
                    break;
                }
            }
        }

        if (!$config['--delivery-uri']) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'You should define --delivery-uri (to see all properties you can use --dry-run)');
        }

        if ($config['--free-hours']) {

            $config['--free-hours'] = explode(',', $config['--free-hours']);
            if (count($config['--free-hours']) > 22) {
                return new \common_report_Report(\common_report_Report::TYPE_ERROR,
                    'You made something wrong with --free-hours (to see all properties you can use --dry-run)');
            }
        }

        /** @var TestTakerDeliveryActivityLogService $service */
        $newFields = 0;
        $events = ['oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState', 'oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated'];

        while ($newFields < $config['--limit']) {

            if ($dryrun && $config['--show-tt']) {
                $report->add(new \common_report_Report(
                    \common_report_Report::TYPE_SUCCESS,
                    "\n" . RdsStorage::TEST_TAKER . ' => #testTaker' . mt_rand(1, $config['--test-taker'])
                    . ";\n" . RdsStorage::DELIVERY . ' => ' . $config['--delivery-uri']
                    . ";\n". RdsStorage::DELIVERY_EXECUTION . ' => #deliveryExecution'
                    . ";\n". RdsStorage::EVENT . ' => ' . $events[mt_rand(0,1)]
                    . ";\n". RdsStorage::TIME . ' => ' . date('Y-m-d H:i:s', $this->getTime($config['--days'], $config['--free-hours']))
                    ));
            } else {
                $this->getPersistence()->insert(RdsStorage::TABLE_NAME, [
                    RdsStorage::TEST_TAKER => '#testTaker' . mt_rand(1, $config['--test-taker']),
                    RdsStorage::DELIVERY => $config['--delivery-uri'],
                    RdsStorage::DELIVERY_EXECUTION => '#deliveryExecution',
                    RdsStorage::EVENT => $events[mt_rand(0,1)],
                    RdsStorage::TIME => date('Y-m-d H:i:s', $this->getTime($config['--days'], $config['--free-hours']))
                ]);
            }
            $newFields++;
        }

        $report->add(new \common_report_Report(\common_report_Report::TYPE_WARNING, 'Generated ' . $newFields . ' row' . ($newFields ? 's' : '')));

        return $report;
    }

    private function getTime($days, $freeHours)
    {
        $time = mt_rand(strtotime('-' . $days . ' days'), strtotime('now'));

        // exclude unexpected hours
        if ($freeHours && in_array(date('H', $time), $freeHours)) {
            return $this->getTime($days, $freeHours);
        }

        return $time;
    }

    private function getCountRows()
    {
        $sql = "SELECT COUNT(id) as cnt FROM " . RdsStorage::TABLE_NAME;
        $stmt = $this->getPersistence()->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['cnt'];
    }

    private function deleteAllRows()
    {
        $sql = "DELETE FROM " . RdsStorage::TABLE_NAME;
        $this->getPersistence()->exec($sql);
    }

    private function getPersistence()
    {
        return \common_persistence_Manager::getPersistence('default');
    }
}
