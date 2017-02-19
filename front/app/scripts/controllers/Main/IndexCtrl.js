(function () {
    'use strict';

    var ctrl = function($scope, $state, User, Place) {

        var ids = [];

        var friends = User.getFriends();

        angular.forEach(friends, function(v, i) {
            ids.push(v.id);
        });

        Place.categories({ids: ids.join(',')}).then(function(result) {
            $scope.rows = result.data;
        });

        $scope.chooseCategory = function(item) {
            $state.go('main.places', {ids: ids.join(','), categoria: item.categoria});
        };

    };

    angular.module('inspinia').
    controller('IndexCtrl', ['$scope', '$state', 'User', 'Place', ctrl]);

})();