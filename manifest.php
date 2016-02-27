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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 *               
 * 
 */               

return array(
    'name' => 'taoMonitoring',
	'label' => 'Statistics and aggregated data',
	'description' => 'Extension for monitoring of the tao events. Fast access to statistics data',
    'license' => 'GPL-2.0',
    'version' => '0.0.1',
	'author' => 'Open Assessment Technologies SA',
	'requires' => array(
        'tao' => '>=2.19.0'
    ),
	'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoMonitoringManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoMonitoringManager', array('ext'=>'taoMonitoring')),
    ),
    'install' => array(
        'php' => array(
            'oat\\taoMonitoring\\scripts\\install\\RegisterTestTakerDeliveryLog',
        )
    ),
    'uninstall' => array(
        'php' => array(
            __DIR__.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'uninstall'.DIRECTORY_SEPARATOR.'TestTakerDeliveryLog.php',
            //'oat\\taoMonitoring\\scripts\\uninstall\\Delivery\\TestTakerLog',
        )
    ),
    'routes' => array(
        'taoMonitoring' => 'oat\\taoMonitoring\\controller'
    ),    
	'constants' => array(
	    # views directory
	    "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,
	    
		#BASE URL (usually the domain root)
		'BASE_URL' => ROOT_URL.'taoMonitoring/',
	    
	    #BASE WWW required by JS
	    'BASE_WWW' => ROOT_URL.'taoMonitoring/views/'
	),
    'extra' => array(
        'structures' => dirname(__FILE__).DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);
