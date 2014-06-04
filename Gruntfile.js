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
                    "assets/main.min.css": "assets/css/main.less"
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
                    'assets/main.min.js': 'assets/js/main.js'
                },
            },
        },
        compress: {
          main: {
            options: {
              archive: 'dxw-security-0_2_1.zip'
            },
            files: [
              { src: ['assets/*'], dest: 'dxw-security/', filter: 'isFile' },
              { src: ['assets/fonts/**', 'dxw-security.php', 'lib/*', 'readme.txt'], dest: 'dxw-security/' },
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

    grunt.registerTask('default', [
        'less',
        'uglify',
        'compress',
    ]);
};
