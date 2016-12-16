<?php

namespace oat\taoMonitoring\scripts;

use oat\oatbox\action\Action;
use common_report_Report as Report;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceManager;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;

class CollectActiveUsersData implements Action
{
    /**
     * @var AssignmentService
     */
    private $assignmentService;

    /**
     * @var TestTakerDeliveryActivityLogInterface
     */
    private $activityLogService;

    public function __invoke($params)
    {
        // to load the constants
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoDeliveryRdf');

        $this->assignmentService = ServiceManager::getServiceManager()->get(AssignmentService::CONFIG_ID);
        $this->activityLogService = ServiceManager::getServiceManager()->get(TestTakerDeliveryActivityLogInterface::SERVICE_ID);

        $report = Report::createInfo('Start collecting active users data');
        $data = [];

        // Get all deliveries and required data
        foreach (DeliveryAssemblyService::singleton()->getRootClass()->getInstances(true) as $delivery) {
            $data[] = $this->collectDeliveryData($delivery);
        }

        if ($data) {
            // Upload generated data to AWS S3 as a JSON file
            /** @var File $file */
            $file = ServiceManager::getServiceManager()
                ->get(FileSystemService::SERVICE_ID)
                ->getDirectory('taoMonitoring')
                ->getFile(date('Y_m_d') .'/monitoring/'. gethostname() .'/active_users_data_'. date('His') .'.json');

            if ($file->write(json_encode($data)) !== false) {
                $report->add(Report::createSuccess('Data successfully saved into ' . $file->getPrefix()));
            } else {
                $report->add(Report::createFailure('Writing data into ' . $file->getPrefix() . ' has been failed.'));
            }
        } else {
            $report->add(Report::createInfo('There was not any data to save.'));
        }

        return $report;
    }

    /**
     * @param \core_kernel_classes_Resource $delivery
     * @return array
     */
    private function collectDeliveryData(\core_kernel_classes_Resource $delivery)
    {
        $data = [
            'label' => $delivery->getLabel(),
            'uri' => $delivery->getUri()
        ];

        $maxExec = current($delivery->getPropertyValues(new \core_kernel_classes_Property(TAO_DELIVERY_MAXEXEC_PROP)));
        $startExec = current($delivery->getPropertyValues(new \core_kernel_classes_Property(TAO_DELIVERY_START_PROP)));
        $endExec = current($delivery->getPropertyValues(new \core_kernel_classes_Property(TAO_DELIVERY_END_PROP)));

        $data['status'] = ($startExec && $endExec && ($startExec >= time() || $endExec <= time())) ? __('Closed') : __('Open');

        $data['startDate'] = $startExec ? (new \DateTime())->setTimestamp($startExec) : '';
        $data['endDate'] = $endExec ? (new \DateTime())->setTimestamp($endExec) : "";

        $data['possibleExecutionsCount'] = $maxExec ? $maxExec * count($this->assignmentService->getAssignedUsers($delivery->getUri())) : 0;

        $data['currentExecutionsCount'] = $this->activityLogService->countDeliveryExecutions($delivery->getUri());

        $activity = $this->activityLogService->getLastActivity($delivery->getUri(), '-30 minutes', true);
        $data['connectedUsers'] = count($activity) ? current($activity)['count'] : 0;

        $data['userActivity'] = $this->activityLogService->getLastActivity($delivery->getUri(), '-1 day');

        return $data;
    }
}