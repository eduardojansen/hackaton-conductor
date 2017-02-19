var version = '1.0.0';

(function () {
    'use strict';

    var modules = [
        'ui.router',
        'ui.bootstrap',
        'oc.lazyLoad',
        'ngResource',
        'angular-ladda',
        'ngValidate',
        'ncy-angular-breadcrumb',
        'facebook',
        'ngStorage',
        'ngCookies',
        'angularMoment',
        'ngSanitize',
        'angular-jwt',
        'oitozero.ngSweetAlert'
    ];

    var config = function config(
        $stateProvider, $urlRouterProvider, $locationProvider, $httpProvider, $ocLazyLoadProvider,
        $resourceProvider, $validatorProvider, laddaProvider, $breadcrumbProvider, FacebookProvider
    ) {

        FacebookProvider.init('250874518704452');

        $resourceProvider.defaults.stripTrailingSlashes = false;

        $ocLazyLoadProvider.config({
            debug: false,
            events: true
        });

        $validatorProvider.setDefaults({
            errorElement: 'span',
            errorClass: 'text-danger',
            highlight: function (element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error');
            }
        });

        laddaProvider.setOption({
            style: 'zoom-in',
            spinnerColor: '#ffffff'
        });

        $httpProvider.interceptors.push(['$q', '$location', function ($q, $location) {
            return {
                request: function (config) {
                    config.withCredentials = true;
                    return config;
                },
                responseError: function (response) {
                    if (response.status === 401) {
                        $location.path('/login');
                    }

                    return $q.reject(response);

                }
            };
        }]);

        $breadcrumbProvider.setOptions({
            includeAbstract: true
        });

        $urlRouterProvider
            .when('', '/')
            .otherwise('/');

        $locationProvider.hashPrefix('!');

        $stateProvider
            .state('login', {
                templateUrl: 'views/pages/login.html',
                url: '/login',
                data: {pageTitle: 'Login', specialClass: 'gray-bg'},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/utils/Alert.js',
                                    'scripts/services/User.js',
                                    'scripts/services/Auth.js',
                                    'scripts/controllers/Auth/AuthCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'AuthCtrl'
            })
            .state('register', {
                url: "/register",
                templateUrl: "views/pages/register.html",
                data: {pageTitle: 'Complete seu cadastro', specialClass: 'gray-bg'},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/utils/Alert.js',
                                    'scripts/services/Usuario.js',
                                    'scripts/services/User.js',
                                    'scripts/controllers/Register/RegisterCtrl.js'
                                ]
                            },{
                                files: ['scripts/plugins/jasny-bootstrap/jasny-bootstrap.min.js']
                            }
                        ]);
                    }
                },
                controller: 'RegisterCtrl'
            })
            .state('account', {
                url: "/account",
                templateUrl: "views/pages/account.html",
                data: {pageTitle: 'Cadastre sua conta', specialClass: 'gray-bg'},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/utils/Alert.js',
                                    'scripts/services/Account.js',
                                    'scripts/services/User.js',
                                    'scripts/services/Usuario.js',
                                    'scripts/controllers/Account/AccountCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'AccountCtrl'
            })
            .state('friends', {
                url: "/friends",
                templateUrl: "views/pages/friends.html",
                data: {pageTitle: 'Encontramos seus amigos aqui', specialClass: 'white-bg'},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/utils/Alert.js',
                                    'scripts/services/Usuario.js',
                                    'scripts/services/User.js',
                                    'scripts/controllers/Friends/FriendsCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'FriendsCtrl'
            })
            .state('main', {
                abstract: true,
                url: '/',
                data: {specialClass: 'skin-1'},
                templateUrl: "views/common/main.html",
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/directives/utils/directives.js',
                                    'scripts/directives/navigation/navigation.js',
                                    'scripts/directives/topnavbar/topnavbar.js',
                                    'scripts/services/User.js',
                                    'scripts/services/utils/Alert.js',
                                    'scripts/services/Usuario.js',
                                    'scripts/services/Auth.js',
                                    'scripts/controllers/Common/MainCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'MainCtrl'
            })
            .state('main.index', {
                url: "",
                templateUrl: "views/pages/index.html",
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/Place.js',
                                    'scripts/controllers/Main/IndexCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'IndexCtrl'
            })
            .state('main.places', {
                url: "places",
                templateUrl: "views/pages/places.html",
                params: { ids: null, categoria: null},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/Usuario.js',
                                    'scripts/services/User.js',
                                    'scripts/controllers/Main/PlaceCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'PlaceCtrl'
            })
            .state('main.detail', {
                url: "detail",
                templateUrl: "views/pages/detail.html",
                params: { ids: null, fk_estabelecimento: null},
                resolve: {
                    loadMyFiles: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name: 'inspinia',
                                files: [
                                    'scripts/services/Usuario.js',
                                    'scripts/services/User.js',
                                    'scripts/controllers/Main/DetailCtrl.js'
                                ]
                            }
                        ]);
                    }
                },
                controller: 'DetailCtrl'
            })
    };

    angular.module('inspinia', modules)
        .constant('APP_CONFIG', {
            baseUrl: '/api',
            version: version,
        })
        .config([
            '$stateProvider', '$urlRouterProvider', '$locationProvider', '$httpProvider', '$ocLazyLoadProvider',
            '$resourceProvider', '$validatorProvider', 'laddaProvider', '$breadcrumbProvider', 'FacebookProvider', config])
        .run(function($rootScope, $state, $window) {
            $rootScope.$state = $state;
        });

})();