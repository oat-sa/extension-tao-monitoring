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

define([
    'jquery',
    'i18n',
    'helpers',
    'd3',
    'c3',
    'css!taoMonitoringCss/delivery-execution.css'
], function ($, __, helpers, d3, c3) {
    'use strict';

    return {

        /**
         * Controller entry point
         */
        start: function start() {

            c3.generate({
                bindto: '#barChar',
                data: {
                    x: 'hour',
                    xFormat: '%Y-%m-%d %H:%M:%S',
                    url: helpers._url('userActivity', 'DeliveryExecutions', 'taoMonitoring', {deliveryUri: $('#barChar').data('delivery')}),
                    mimeType: 'json',
                    keys: {
                        value: ['hour', 'count']
                    },
                    type: 'bar',
                    labels: true
                },
                axis: {
                    y: {
                        label: {
                            text: __('Active users'),
                            position: 'top'
                        }
                    },
                    x: {
                        label: {
                            text: __('Hours'),
                            position: 'bottom center'
                        },
                        type: 'timeseries',
                        // if true, treat x value as localtime (Default)
                        // if false, convert to UTC internally
                        localtime: true,
                        tick: {
                            format: '%H.00'
                        }
                    }
                },
                legend: {
                    hide: true
                }
            });

        }
    };
});
