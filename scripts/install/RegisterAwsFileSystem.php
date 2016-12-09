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

        try{
            $service = $serviceManager->get(FileSystemService::SERVICE_ID);
            $options = $service->getOptions();
        } catch (ServiceNotFoundException $e) {
            //do nothing
        }

        $options[FileSystemService::OPTION_ADAPTERS]['taoAwsS3'] = [
            'class'   => 'oat\\awsTools\\AwsFlyWrapper',
            'options' => [
                [
                    'cache'  => true,
                    'bucket' => '',
                    'client' => 'generis/awsClient',
                    'prefix' => 'tao'
                ]
            ]
        ];

        $newService = new AwsFileSystemService($options);
        $serviceManager->register(FileSystemService::SERVICE_ID, $newService);
    }
}