(function () {
    'use strict';

    var ctrl = function($scope, $state, Auth, User, Alert) {

        var authenticate = function(result) {
            Auth.authenticate({
                app_id: result.userID,
                token: result.accessToken
            }).then(function(res) {
                User.setToken( res.headers('token') );

                User.me().then(function(data) {
                    User.set(data.data);
                    User.setFriends();
                    $state.go('main.index');
                    $scope.loadingBtn = false;
                });
            }).catch(function(error) {
                Alert.error('Ops!', error.data.errorMessage);
                $scope.loadingBtn = false;
            });
        };

        $scope.login = function() {
            $scope.loadingBtn = true;

            Auth.login().then(function(result) {

                delete $scope.errorMessage;

                if ( angular.isDefined( User.get() ) ) {
                    if ( User.get().app_id == result.userID ) {
                        $scope.loadingBtn = false;
                        $state.go('main.index');
                    } else {
                        User.delete();
                        authenticate(result);
                    }
                } else {
                    authenticate(result);
                }
            }).catch(function(error) {
                $scope.errorMessage = error.data.errorMessage;
                $scope.loadingBtn = false;
            });
        }

    };

    angular.module('inspinia').
        controller('AuthCtrl', ['$scope', '$state', 'Auth', 'User', 'Alert', ctrl]);

})();