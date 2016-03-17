<?php
/**
 * Copyright (c) 2016 Open Assessment Technologies, S.A.
 *
 * @author A.Zagovorichev, <zagovorichev@1pt.com>
 */

namespace oat\taoMonitoring\scripts\tools;

use oat\oatbox\action\Action;
use common_report_Report as Report;
use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\DeliveryDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade\Updater;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use taoDelivery_models_classes_execution_ServiceProxy;

class UpdateTestTakerDeliveryLog implements Action
{
    
    public function __invoke($params)
    {
        $service = new Service(['persistence' => 'default']);
        
        $resultsService = ResultsService::singleton();
        $executionService = taoDelivery_models_classes_execution_ServiceProxy::singleton();
        $deliveryAssemblyService = DeliveryAssemblyService::singleton();

        $aggregator = new DeliveryDataAggregator(
            $resultsService,
            $executionService,
            $deliveryAssemblyService
        );

        (new Updater(
            $service,
            new TmpStorage(),
            new RdsStorage('default'),
            $aggregator
        ))->execute();

        $report = new Report(Report::TYPE_INFO,'Ran');
        return $report;

    }
}
