<?php
/**
 * Update storage from CLI (init updater, still not tested with TMP Storage updating, that means don't use after init update)
 * 
 */


if(PHP_SAPI == 'cli'){
    $_SERVER['HTTP_HOST'] = 'http://localhost';
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../../..';
}

require_once dirname(__FILE__). '/../../tao/includes/class.Bootstrap.php';

$bootStrap = new BootStrap('taoMonitoring');
$bootStrap->start();

use oat\taoMonitoring\model\TestTakerDeliveryLog\aggregator\DeliveryDataAggregator;
use oat\taoMonitoring\model\TestTakerDeliveryLog\Service;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\RdsStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\storage\TmpStorage;
use oat\taoMonitoring\model\TestTakerDeliveryLog\upgrade\Updater;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;

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
