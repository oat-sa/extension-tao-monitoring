module.exports = function(grunt) {
    'use strict';

    var sass    = grunt.config('sass') || {};
    var watch   = grunt.config('watch') || {};
    var notify  = grunt.config('notify') || {};
    var root    = grunt.option('root') + '/taoMonitoring/views/';

    sass.taomonitoring = { };
    sass.taomonitoring.files = { };
    sass.taomonitoring.files[root + 'css/delivery-execution.css'] = root + 'scss/delivery-execution.scss';

    watch.taomonitoringsass = {
        files : [root + 'scss/**/*.scss'],
        tasks : ['sass:taomonitoring', 'notify:taomonitoringsass'],
        options : {
            debounceDelay : 1000
        }
    };

    notify.taomonitoringsass = {
        options: {
            title: 'Grunt SASS',
            message: 'SASS files compiled to CSS'
        }
    };

    grunt.config('sass', sass);
    grunt.config('watch', watch);
    grunt.config('notify', notify);

    //register an alias for main build
    grunt.registerTask('taomonitoringsass', ['sass:taomonitoring']);
};
