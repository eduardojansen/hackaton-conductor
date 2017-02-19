(function () {
    'use strict';

    var ctrl = function($scope, $state, $filter, User, Usuario, Alert) {

        console.log( $state.params );

        if ( ($state.params.ids == null) || ($state.params.categoria == null) ) {
            Alert.warning('Oooopss', 'Ainda não temos informações suficientes para processar suas sugestões.', function() {
                $state.go('main.index');
            });
        } else {

            $scope.ids = $state.params.ids;
            $scope.categoria = $state.params.categoria;

            $scope.filters = [{
                id: 'qtde',
                label: 'Mais Visitados'
            },{
                id: 'valor_medio',
                label: 'Menor Preço'
            }];

            Usuario.sugestoes($state.params).then(function(result) {
                var data = result.data;

                angular.forEach(data, function(value, index) {

                    value.friends = [];

                    var friends = User.getFriends();

                    angular.forEach(value.friends_id, function(v, i) {
                        value.friends.push( $filter('filter')(friends, {id: v})[0] );
                    });

                });

                $scope.rows = data;
            });

            $scope.filtrar = function(ids, categoria, order) {

                Usuario.sugestoes({
                    ids: ids,
                    categoria: categoria,
                    order: order.id,
                    coordenadas: "-7.1490573,-34.8437913"
                }).then(function(result) {
                    var data = result.data;

                    angular.forEach(data, function(value, index) {

                        value.friends = [];

                        var friends = User.getFriends();

                        angular.forEach(value.friends_id, function(v, i) {
                            value.friends.push( $filter('filter')(friends, {id: v})[0] );
                        });

                    });

                    $scope.rows = data;
                });
            }

        }

        $scope.choosePlace = function(item) {
            $state.go('main.detail', {ids: $scope.ids, fk_estabelecimento: item.codigo});
        }

    };

    angular.module('inspinia').
    controller('PlaceCtrl', ['$scope', '$state', '$filter', 'User', 'Usuario', 'Alert', ctrl]);

})();