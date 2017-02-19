'use strict';
module.exports = function (grunt) {

    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    var proxySnippet = require('grunt-connect-proxy/lib/utils').proxyRequest;

    var appConfig = {
        app: 'app',
        dist: 'dist'
    };

    grunt.initConfig({
        project: appConfig,
        connect: {
            options: {
                port: 9000,
                hostname: '*',
                livereload: 35729
            },
            proxies: [
                {
                    context: '/api',
                    host: 'localhost',
                    port: 8000,
                    changeOrigin: true,
                    https: false,
                    rewrite: {
                        '^/api': '/'
                    }
                }
            ],
            livereload: {
                options: {
                    middleware: function (connect) {

                        var middlewares = [];

                        middlewares.push(connect.static('.tmp'));
                        middlewares.push(connect().use(
                            '/bower_components',
                            connect.static('./bower_components')
                        ));
                        middlewares.push(connect.static(appConfig.app));

                        middlewares.push(proxySnippet);

                        return middlewares;

                    }
                }
            },
            dist: {
                options: {
                    open: true,
                    base: '<%= project.dist %>'
                }
            }
        },
        less: {
            development: {
                options: {
                    compress: true,
                    optimization: 2
                },
                files: {
                    "app/styles/style.css": "app/less/style.less"
                }
            }
        },
        watch: {
            styles: {
                files: ['app/less/**/*.less'],
                tasks: ['less', 'copy:styles'],
                options: {
                    nospawn: true,
                    livereload: '<%= connect.options.livereload %>'
                },
            },
            js: {
                files: ['<%= project.app %>/scripts/{,*/}{,*/}*.js'],
                options: {
                    livereload: '<%= connect.options.livereload %>'
                }
            },
            livereload: {
                options: {
                    livereload: '<%= connect.options.livereload %>'
                },
                files: [
                    '<%= project.app %>/**/*.html',
                    '.tmp/styles/{,*/}*.css',
                    '<%= project.app %>/images/{,*/}*.{png,jpg,jpeg,gif,webp,svg}'
                ]
            }
        },
        uglify: {
            options: {
                mangle: false
            }
        },
        clean: {
            dist: {
                files: [{
                    dot: true,
                    src: [
                        '.tmp',
                        '<%= project.dist %>/{,*/}*',
                        '!<%= project.dist %>/.git*'
                    ]
                }]
            },
            server: '.tmp'
        },
        copy: {
            dist: {
                files: [
                    {
                        expand: true,
                        dot: true,
                        cwd: '<%= project.app %>',
                        dest: '<%= project.dist %>',
                        src: [
                            '*.{ico,png,txt}',
                            '.htaccess',
                            '*.html',
                            'views/{,*/}*.html',
                            'styles/patterns/*.*',
                            'img/{,*/}*.*'
                        ]
                    },
                    {
                        expand: true,
                        dot: true,
                        cwd: 'bower_components/fontawesome',
                        src: ['fonts/*.*'],
                        dest: '<%= project.dist %>'
                    },
                    {
                        expand: true,
                        dot: true,
                        cwd: 'bower_components/bootstrap',
                        src: ['fonts/*.*'],
                        dest: '<%= project.dist %>'
                    },
                ]
            },
            styles: {
                expand: true,
                cwd: '<%= project.app %>/styles',
                dest: '.tmp/styles/',
                src: '{,*/}*.css'
            }
        },
        filerev: {
            dist: {
                src: [
                    '<%= project.dist %>/scripts/{,*/}*.js',
                    '<%= project.dist %>/styles/{,*/}*.css',
                    '<%= project.dist %>/styles/fonts/*'
                ]
            }
        },
        htmlmin: {
            dist: {
                options: {
                    collapseWhitespace: true,
                    conservativeCollapse: true,
                    collapseBooleanAttributes: true,
                    removeCommentsFromCDATA: true,
                    removeOptionalTags: true
                },
                files: [{
                    expand: true,
                    cwd: '<%= project.dist %>',
                    src: ['*.html', 'views/{,*/}*.html'],
                    dest: '<%= project.dist %>'
                }]
            }
        },
        useminPrepare: {
            html: 'app/index.html',
            options: {
                dest: 'dist'
            }
        },
        usemin: {
            html: ['dist/index.html']
        },
        bowercopy: {
            options: {
                clean: true
            },
            plugins: {
                options: {
                    destPrefix: 'app/scripts/plugins'
                },
                files: {
                    'moment/moment.min.js': 'moment/min/moment.min.js',
                    'sweetalert/sweetalert.min.js': 'sweetalert/dist/sweetalert.min.js',
                    'sweetalert/ngSweetAlert/SweetAlert.js': 'ngSweetAlert/SweetAlert.js',
                    'jasny-bootstrap/jasny-bootstrap.min.js': 'jasny-bootstrap/dist/js/jasny-bootstrap.min.js'
                }
            },
            css: {
                options: {
                    destPrefix: 'app/styles/plugins'
                },
                files: {
                    'sweetalert/sweetalert.css': 'sweetalert/dist/sweetalert.css',
                }
            }
        },
    });

    grunt.registerTask('live', [
        'clean:server',
        'copy:styles',
        'configureProxies',
        'connect:livereload',
        'watch'
    ]);

    grunt.registerTask('server', [
        'build',
        'connect:dist:keepalive'
    ]);

    grunt.registerTask('build', [
        'clean:dist',
        'less',
        'useminPrepare',
        'concat',
        'copy:dist',
        'cssmin',
        'uglify',
        'filerev',
        'usemin',
        'htmlmin'
    ]);

};
