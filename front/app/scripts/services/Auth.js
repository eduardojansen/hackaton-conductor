(function () {
    'use strict';

    angular
        .module('inspinia')
        .service('Auth', ['Facebook', 'User', '$q', '$http', 'APP_CONFIG', function (Facebook, User, $q, $http, APP_CONFIG) {

            var Service = {
                authenticate: function(data) {
                    return $http.post(APP_CONFIG.baseUrl + '/authenticate', data);
                },
                me: function() {
                    Facebook.api('/me', function(response) {
                        console.log( response );
                    });
                },
                login: function() {

                    var deferred = $q.defer();

                    Facebook.login(function(response) {
                        if (response.status === 'connected') {
                            deferred.resolve(response.authResponse);
                        } else {
                            deferred.reject({
                                data: {
                                    errorMessage: 'Usuário não autorizou o BoraLá'
                                }
                            });
                        }
                    }, {scope: 'user_birthday, user_relationships, user_hometown, user_location, user_friends, user_about_me, email, public_profile, basic_info'});

                    return deferred.promise;
                },
                checkLogin: function() {

                    var deferred = $q.defer();

                    Facebook.getLoginStatus(function(response) {
                        if (response.status === 'connected') {
                            deferred.resolve(true);
                        } else {
                            deferred.reject(false);
                        }
                    });

                    return deferred.promise;
                }
            };

            return Service;

        }]);
})();