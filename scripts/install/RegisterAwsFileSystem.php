<?php
namespace oat\taoMonitoring\scripts\install;

use oat\awsTools\AwsFileSystemService;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceNotFoundException;

/**
 * Class RegisterAwsFileSystem
 *
 * @package oat\taoMonitoring
 */
class RegisterAwsFileSystem extends \common_ext_action_InstallAction
{
    public function __invoke($params)
    {
        $serviceManager = $this->getServiceManager();
        $service = $serviceManager->get(FileSystemService::SERVICE_ID);
        $service->createFileSystem('taoMonitoring');
        $serviceManager->register(FileSystemService::SERVICE_ID, $service);
    }
}