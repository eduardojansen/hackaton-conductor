(function () {
    'use strict';

    var ctrl = function($scope, $state, Alert, Account, User, Usuario) {

        $scope.row = {};

        Account.bancos().then(function(result) {
            $scope.bancos = result.data;
        });

        $scope.addAccount = function(item) {

            $scope.loadingMessage = "Salvando dados bancários...";

            var data = angular.copy(item);
            data.fk_usuario = User.get().codigo;
            data.banco = data.banco.name;

            Account.add(data).then(function(result) {
                $scope.updateTransarions();
            }).catch(function(error) {
                Alert.error('', error.data.errorMessage);
                delete $scope.loadingMessage;
                $scope.loadingSubmit = false;
            });

        };

        $scope.updateTransarions = function() {

            $scope.loadingMessage = "Atualizando transações...";

            Usuario.updateTransations().then(function (result) {
                Alert.success('Sucesso', result.data.message, function() {
                    $state.go('main.index');
                });

                $scope.loadingSubmit = false;
            }).catch(function(error) {
                Alert.error('', error.data.errorMessage);
                delete $scope.loadingMessage;
            })

        };

    };

    angular.module('inspinia').
        controller('AccountCtrl', ['$scope', '$state', 'Alert', 'Account', 'User', 'Usuario', ctrl]);

})();