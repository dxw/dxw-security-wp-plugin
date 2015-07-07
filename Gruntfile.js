//
// == Installation ==
//
// Install the grunt command-line tool (-g puts it in /usr/local/bin):
// % sudo npm install -g grunt-cli
//
// Install all the packages required to build this:
// (Packages will be installed in ./node_modules - don't accidentally commit this)
// % cd wp-content/themes/theme-name
// % npm install
//
// == Building ==
//
// % grunt
//
// Watch for changes:
// % grunt watch
//
// Create zip file
// % grunt compress
//

module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            dist: {
                options: {
                    yuicompress: true,
                },
                files: {
                    "assets/main.min.css": [ "assets/css/main.less", "assets/css/settings.less" ]
                },
            },
        },
        uglify: {
            dist: {
                options: {
                    preserveComments: 'some',
                    compress: false,
                },
                files: {
                    'assets/main.min.js': [ 'assets/js/main.js', 'assets/js/registration_ajax.js' ]
                },
            },
        },
        copy: {
          main: {
            nonull: true,
            files: [
              { src: ['assets/*'], dest: 'build/', filter: 'isFile' },
              { src: ['assets/fonts/**', 'mongoosewp.php', 'lib/**', 'readme.txt'], dest: 'build/' },
            ]
          }
        },
        compress: {
          main: {
            nonull: true,
            options: {
              archive: 'mongoosewp-0.1.0.zip'
            },
            files: [
              { cwd: 'build', src: ['**'], dest: 'mongoosewp/', filter: 'isFile' },
            ]
          }
        },
        watch: {
            css: {
                files: ['assets/css/**/*.less'],
                tasks: ['less'],
            },
            js: {
                files: ['assets/js/**/*.js'],
                tasks: ['uglify'],
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', [
        'less',
        'uglify',
    ]);

    grunt.registerTask('build', ['copy', 'compress']);
};
