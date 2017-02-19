(function () {
    'use strict';

    var ctrl = function($scope, $state, Auth, User, Usuario, Alert) {
        Auth.checkLogin().then(function(result) {

            if( angular.isDefined( User.get() ) ) {
                if ( User.get().status == 'pending' ) {
                    $state.go('register');
                } else {
                    $scope.User = User.get();

                    $scope.loadingMessage = "Atualizando transações...";

                    Usuario.updateTransations();
                }
            } else {
                $state.go('login');
            }

        }).catch(function(error) {
            $state.go('login');
        });

        $scope.logout = function() {
            User.delete();
            $state.go('login');
        };

    };

    angular.module('inspinia').
    controller('MainCtrl', ['$scope', '$state', 'Auth', 'User', 'Usuario', 'Alert', ctrl]);

})();