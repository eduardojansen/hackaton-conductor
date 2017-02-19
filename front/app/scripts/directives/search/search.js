/**
 * Created by alexandre on 13/10/16.
 */
(function () {
    'use strict';

    angular.module('hackaton').directive('search', function() {
        return {
            templateUrl:'scripts/directives/search/template.html?v='+version, // jshint ignore:line
            restrict: 'E',
            replace: true,
            scope: {
                label: '@',
                action: '&'
            },
            controller: function($scope) {

                $scope.doAction = function(searchParams) {
                    if ( searchParams ) {
                        $scope.action({params: searchParams});
                    }
                };

            },
            link: function(scope, elem) {


                elem.find('#btnSearch').bind('click', function() {
                    $('#searchForm').validate({
                        rules: {
                            search: 'required'
                        }
                    });

                });
            }
        };
    });

})();