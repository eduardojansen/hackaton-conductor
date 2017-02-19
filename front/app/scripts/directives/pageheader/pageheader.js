(function () {
    'use strict';

    angular.module('foccusApp').directive('pageHeader', function() {
        return {
            templateUrl:'scripts/directives/pageheader/pageheader.html?v='+version, // jshint ignore:line
            restrict: 'E',
            replace: true,
            controller: function($scope, $state) {

                $scope.currentPage = $state.$current;

            }
        };
    });
    
})();