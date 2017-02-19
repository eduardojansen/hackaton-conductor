(function () {
    'use strict';

    angular.module('inspinia').directive('navigation', function() {
        return {
            templateUrl:'scripts/directives/navigation/navigation.html?v='+version,
            restrict: 'E',
            replace: true,
            link: function() {

                $('.sidebar-collapse').slimScroll({
                    height: '100%',
                    railOpacity: 0.9
                });

            }
        }
    });
})();