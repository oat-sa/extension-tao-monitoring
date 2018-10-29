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
    'version' => '2.0.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'generis'        => '>=2.15.0',
        'tao'            => '>=21.0.0',
        'taoDelivery'    => '>=11.0.0',
        'taoDeliveryRdf' => '>=6.0.0',
        'taoOutcomeUi'   => '>=7.0.0',
        'taoQtiTest'     => '>=29.0.0',
    ),
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoMonitoringManager',
    'acl' => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoMonitoringManager', array('ext'=>'taoMonitoring')),
    ),
    'install' => array(
        'php' => array(
            \oat\taoMonitoring\scripts\install\RegisterRdsTestTakerDeliveryActivityLog::class,
            \oat\taoMonitoring\scripts\install\RegisterRdsDeliveryLog::class
        )
    ),
    'update' => 'oat\\taoMonitoring\\scripts\\update\\Updater',
    'routes' => array(
        'taoMonitoring' => 'oat\\taoMonitoring\\controller'
    ),
    'constants' => array(
        # views directory
        "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL.'taoMonitoring/'
    ),
    'extra' => array(
        'structures' => dirname(__FILE__).DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR.'structures.xml',
    )
);
