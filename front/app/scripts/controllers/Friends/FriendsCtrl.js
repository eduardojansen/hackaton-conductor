(function () {
    'use strict';

    var ctrl = function($scope, $state, Alert, Facebook, Usuario) {

        var ids = [];

        Facebook.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                Facebook.api('/'+response.authResponse.userID+'/friends?fields=id,name,picture', function(resp) {

                    angular.forEach(resp.data, function(v, i) {
                        ids.push(v.id);
                    });

                    $scope.rows = resp.data;

                });
            } else {
                $state.go('login');
            }
        });

        $scope.updateFriends = function() {

            $scope.loadingSubmit = true;

            Usuario.friends({ids: ids.join(',')}).then(function(resp) {
                Alert.success('Sucesso', resp.data.message, function() {
                    $scope.loadingSubmit = false;
                });
            }).catch(function(error) {
                Alert.error('', error.data.errorMessage);
                $scope.loadingSubmit = false;
            });
        };

    };

    angular.module('inspinia').
        controller('FriendsCtrl', ['$scope', '$state', 'Alert', 'Facebook', 'Usuario', ctrl]);

})();