(function () {
    'use strict';

    var ctrl = function($scope, $state, $filter, User, Usuario, Alert) {

        if ( ($state.params.ids == null) || ($state.params.fk_estabelecimento == null) ) {
            $state.go('main.index');
        } else {

            $scope.friendsToInvite = [];

            Usuario.sugestoes($state.params).then(function(result) {
                var data = result.data;

                angular.forEach(data, function(value, index) {
                    value.friends = [];
                    var friends = User.getFriends();
                    angular.forEach(value.friends_id, function(v, i) {
                        var f = $filter('filter')(friends, {id: v})[0];
                        f.selected = false;
                        value.friends.push( f );
                    });
                });

                $scope.row = data[0];

            });

            $scope.updateFriendsInvite = function(item) {
                $scope.friendsToInvite = $filter('filter')($scope.row.friends, {selected: true});
            };

            $scope.inviteFriends = function() {
                Alert.success('', 'O convite para '+$scope.row.nome+' foi enviado para todos os amigos selecionados.', function() {
                    $state.go('main.index');
                });
            }

        }

    };

    angular.module('inspinia').
    controller('DetailCtrl', ['$scope', '$state', '$filter', 'User', 'Usuario', 'Alert', ctrl]);

})();