(function () {
    'use strict';

    angular.module('inspinia')
        .service('User', function ($http, $localStorage, $timeout, $cookies, $filter, APP_CONFIG, jwtHelper, Facebook) {

        return {
            set: function (data) {
                $localStorage.User = data;
            },
            setToken: function(token) {
                $localStorage.Token = token;
            },
            setFriends: function() {
                Facebook.getLoginStatus(function(response) {
                    if (response.status === 'connected') {
                        Facebook.api('/'+response.authResponse.userID+'/friends?fields=id,name,picture', function(resp) {
                            $localStorage.Friends = resp.data;
                        });
                    }
                });
            },
            getFriends: function() {
                return $localStorage.Friends;
            },
            get: function () {
                return $localStorage.User;
            },
            update: function (callback) {
                var obj = this;
                obj.me().then(function (data) {
                    obj.set(data.data);

                    if (callback) {
                        callback();
                    }
                });
            },
            delete: function () {
                delete $localStorage.User;
                delete $localStorage.Token;
                delete $localStorage.Friends;

                return $http.delete(APP_CONFIG.baseUrl + '/rest/endsession');
            },
            me: function () {
                return $http.get(APP_CONFIG.baseUrl + '/rest/me');
            },
            getPermissions: function () {
                var token = $localStorage.Token;

                if (angular.isDefined(token)) {

                    var tokenPayload = jwtHelper.decodeToken(token);

                    return tokenPayload.data.permissions;
                }

                return false;

            },
            checkUserAccess: function (module, action) {
                var permissions = this.getPermissions();

                if (permissions === false) {
                    return permissions;
                }

                var permitted = false;

                angular.forEach(permissions, function (value, key) {
                    if (key === module) {

                        if (action) {
                            var found = $filter('filter')(value.allow, action, true);

                            permitted = (found.length > 0);
                        } else {
                            permitted = true;
                        }

                    }
                });

                return permitted;
            }
        };
    });
})();