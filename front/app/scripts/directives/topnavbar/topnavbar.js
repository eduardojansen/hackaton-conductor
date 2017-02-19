(function () {
	'use strict';

	angular.module('inspinia').directive('topnavbar', function() {
	    return {
	        templateUrl:'scripts/directives/topnavbar/topnavbar.html?v='+version,
	        restrict: 'E',
	        replace: true,
	        controller: function($scope) {

	        }
	    }
	});
})();