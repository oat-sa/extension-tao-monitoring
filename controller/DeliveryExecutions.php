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

namespace oat\taoMonitoring\controller;


use common_exception_IsAjaxAction;
use core_kernel_classes_Resource;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use oat\taoMonitoring\model\TestTakerDeliveryActivityLogInterface;
use oat\taoOutcomeUi\model\ResultsService;
use tao_actions_SaSModule;
use tao_helpers_Request;
use tao_helpers_Uri;

class DeliveryExecutions extends tao_actions_SaSModule
{

    /**
     * @var DeliveryAssemblyService
     */
    private $deliveryService;

    /**
     * @var AssignmentService
     */
    private $assignmentService;

    /**
     * @var \taoDelivery_models_classes_execution_ServiceProxy
     */
    private $executionService;

    /**
     * @var TestTakerDeliveryActivityLogInterface
     */
    private $activityLogService;
    
    /**
     * constructor: initialize the service and the default data
     */
    public function __construct()
    {
        parent::__construct();

        $this->service = ResultsService::singleton();
        $this->deliveryService = DeliveryAssemblyService::singleton();
        $this->assignmentService = $this->getServiceManager()->get(AssignmentService::CONFIG_ID);
        $this->executionService = \taoDelivery_models_classes_execution_ServiceProxy::singleton();
        $this->activityLogService = $this->getServiceManager()->get(TestTakerDeliveryActivityLogInterface::SERVICE_ID);
        $this->defaultData();
    }

    /**
     * Ontology data for deliveries (not results, so use deliveryService->getRootClass)
     * @throws common_exception_IsAjaxAction
     */
    public function getOntologyData()
    {
        if (!tao_helpers_Request::isAjax()) {
            throw new common_exception_IsAjaxAction(__FUNCTION__);
        }

        $options = array(
            'subclasses' => true,
            'instances' => true,
            'highlightUri' => '',
            'chunk' => false,
            'offset' => 0,
            'limit' => 0
        );

        if ($this->hasRequestParameter('loadNode')) {
            $options['uniqueNode'] = $this->getRequestParameter('loadNode');
        }

        if ($this->hasRequestParameter("selected")) {
            $options['browse'] = array($this->getRequestParameter("selected"));
        }

        if ($this->hasRequestParameter('hideInstances')) {
            if ((bool)$this->getRequestParameter('hideInstances')) {
                $options['instances'] = false;
            }
        }
        if ($this->hasRequestParameter('classUri')) {
            $clazz = $this->getCurrentClass();
            $options['chunk'] = !$clazz->equals($this->deliveryService->getRootClass());
        } else {
            $clazz = $this->deliveryService->getRootClass();
        }

        if ($this->hasRequestParameter('offset')) {
            $options['offset'] = $this->getRequestParameter('offset');
        }

        if ($this->hasRequestParameter('limit')) {
            $options['limit'] = $this->getRequestParameter('limit');
        }

        //generate the tree from the given parameters
        $tree = $this->getClassService()->toTree($clazz, $options);

        $tree = $this->addPermissions($tree);

        function sortTree(&$tree)
        {
            usort($tree, function ($a, $b) {
                if (isset($a['data']) && isset($b['data'])) {
                    if ($a['type'] != $b['type']) {
                        return ($a['type'] == 'class') ? -1 : 1;
                    } else {
                        return strcasecmp($a['data'], $b['data']);
                    }
                }
                return 0;
            });
        }

        if (isset($tree['children'])) {
            sortTree($tree['children']);
        } elseif (array_values($tree) === $tree) {//is indexed array
            sortTree($tree);
        }

        //expose the tree
        $this->returnJson($tree);
    }

    /**
     * @return ResultsService
     */
    protected function getClassService()
    {
        return $this->service;
    }

    /**
     * Action called on click on a delivery (class) construct and call the view to see the table of
     * all delivery execution for a specific delivery
     */
    public function index()
    {
        $model = array(
            array(
                'id' => 'ttaker',
                'label' => __('Test Taker'),
                'sortable' => false
            ),
            array(
                'id' => 'time',
                'label' => __('Start Time'),
                'sortable' => false
            )
        );

        $deliveryService = DeliveryAssemblyService::singleton();
        $delivery = new core_kernel_classes_Resource($this->getRequestParameter('id'));
        if ($delivery->getUri() !== $deliveryService->getRootClass()->getUri()) {

            try {
                // display delivery
                $implementation = $this->getClassService()->getReadableImplementation($delivery);

                $this->getClassService()->setImplementation($implementation);


                $this->setData('uri', tao_helpers_Uri::encode($delivery->getUri()));
                $this->setData('title', $delivery->getLabel());


                $deliveryProps = $delivery->getPropertiesValues(array(
                    new \core_kernel_classes_Property(TAO_DELIVERY_MAXEXEC_PROP),
                    new \core_kernel_classes_Property(TAO_DELIVERY_START_PROP),
                    new \core_kernel_classes_Property(TAO_DELIVERY_END_PROP),
                ));

                $propMaxExec = current($deliveryProps[TAO_DELIVERY_MAXEXEC_PROP]);
                $propStartExec = current($deliveryProps[TAO_DELIVERY_START_PROP]);
                $propEndExec = current($deliveryProps[TAO_DELIVERY_END_PROP]);

                $allowedExecutions = (!(is_object($propMaxExec)) or ($propMaxExec == "")) ? 0 : (int)$propMaxExec->literal;

                $startDate = (!(is_object($propStartExec)) or ($propStartExec == "")) ? null : $propStartExec->literal;
                $endDate = (!(is_object($propEndExec)) or ($propEndExec == "")) ? null : $propEndExec->literal;

                // status
                $status = __('Open');

                if ($startDate && $endDate) {

                    $startDate = date_create('@' . $startDate);
                    $endDate = date_create('@' . $endDate);

                    if (date_create() <= $startDate || date_create() >= $endDate) {
                        $status = __('Closed');
                    }
                }

                $this->setData('status', $status);

                // date range
                $this->setData('startDate', $startDate);
                $this->setData('endDate', $endDate);

                // possible execution count
                $possibleExecutions = 0;
                if ($allowedExecutions) {
                    // test takers * allowed
                    $assignedUsers = count($this->assignmentService->getAssignedUsers($delivery->getUri()));
                    $possibleExecutions = $allowedExecutions * $assignedUsers;
                }

                $this->setData('possibleExecutionsCount', $possibleExecutions);

                $countExecutions = $this->activityLogService->countDeliveryExecutions($delivery->getUri());
                $this->setData('countExecutions', $countExecutions);

                // count connected users
                $activeUsers = 0;
                $activity = $this->activityLogService->getLastActivity($delivery->getUri(), '-30 minutes', true);
                if (count($activity)) {
                    $activeUsers = current($activity)['count'];
                }

                $this->setData('connectedUsers', $activeUsers);
                $this->setData('deliveryUri', $delivery->getUri());
                $this->setData('model', $model);

                $possible = $possibleExecutions ? $possibleExecutions : $countExecutions * 2;
                $limit = $possibleExecutions ? $possibleExecutions . ' ' . __('Total Expected') : __('Unlimited');

                $percent = 0;
                if ($possible) {
                    $percent = 100 * $countExecutions / $possible;
                }

                $this->setData('possible', $possible);
                $this->setData('limit', $limit);
                $this->setData('percent', $percent);

                $this->setView('DeliveryExecutions' . DIRECTORY_SEPARATOR . 'index.tpl');

            } catch (\common_exception_Error $e) {
                $this->setData('type', 'error');
                $this->setData('error', $e->getMessage());
                $this->setView('index.tpl');
            }

        } else {
            $this->setData('type', 'info');
            $this->setData('error', __('No tests have been taken yet. As soon as a test-taker will take a test his results will be displayed here.'));
            $this->setView('index.tpl');
        }
    }
    
    public function userActivity()
    {
        $deliveryUri = $this->getRequestParameter('deliveryUri');
        // for last 24 hours by default
        $this->returnJson($this->activityLogService->getLastActivity($deliveryUri, '-1 day'), 200);
    }
}
