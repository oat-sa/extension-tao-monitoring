module.exports = function(grunt) {
    'use strict';

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';

    var paths = {
        'tao' : root + '/tao/views/js',
        'taoMonitoring' : root + '/taoMonitoring/views/js',
        'taoMonitoringCss' : root + '/taoMonitoring/views/css'
    };

    /**
     * Remove bundled and bundling files
     */
    clean.taomonitoringbundle = [out];

    /**
     * Compile tao files into a bundle
     */
    requirejs.taomonitoringbundle = {
        options: {
            baseUrl : '../js',
            dir : out,
            mainConfigFile : './config/requirejs.build.js',
            paths : paths,
            modules : [{
                name: 'taoMonitoring/controller/routes',
                include : ext.getExtensionsControllers(['taoMonitoring']),
                exclude : ['mathJax'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taomonitoringbundle = {
        files: [
            { src: [ out + '/taoMonitoring/controller/routes.js'],  dest: root + '/taoMonitoring/views/js/controllers.min.js' },
            { src: [ out + '/taoMonitoring/controller/routes.js.map'],  dest: root + '/taoMonitoring/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('copy', copy);
    grunt.config('requirejs', requirejs);

    // bundle task
    grunt.registerTask('taomonitoringbundle', ['clean:taomonitoringbundle', 'requirejs:taomonitoringbundle', 'copy:taomonitoringbundle']);
};
