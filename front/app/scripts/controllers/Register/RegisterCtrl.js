(function () {
    'use strict';

    var ctrl = function($scope, $state, User, Alert, Facebook, Usuario) {

        if ( User.get().status != 'pending' ) {
            $state.go('main.index');
        }

        $scope.loadingMessage = "Buscando dados no Facebook...";

        Facebook.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                Facebook.api('/me?fields=id,name,email', function(response) {

                    $scope.row = {
                        nome: response.name,
                        email: response.email
                    };

                    Facebook.api('/'+response.id+'/picture?type=large', function(res) {
                        $scope.row.anexo = res.data.url;

                        delete $scope.loadingMessage;
                    });
                });
            } else {
                $state.go('login');
            }
        });

        $scope.completeRegister = function(item) {

            var data = {};

            angular.merge(data, User.get(), item);

            delete data.app_id;

            data.status = 'active';

            $scope.loadingMessage = "Finalizando cadastro...";

            Usuario.update(data.codigo, data).then(function(resp) {
                Alert.success('Sucesso', resp.data.message, function() {
                    User.update(function() {
                        delete $scope.loadingMessage;
                        $state.go('account');
                    });
                    $scope.loadingSubmit = false;
                });
            }).catch(function(error) {
                Alert.error('', error.data.errorMessage);
                $scope.loadingSubmit = false;
            });

        }

    };

    angular.module('inspinia').
        controller('RegisterCtrl', ['$scope', '$state', 'User', 'Alert', 'Facebook', 'Usuario', ctrl]);

})();