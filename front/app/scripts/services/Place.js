(function () {
    'use strict';
    angular.module('inspinia').factory('Place', function ($http, APP_CONFIG) {

        var Service = {
            categories: function(data) {
                return $http.post(APP_CONFIG.baseUrl + '/rest/estabelecimentos/categorias', data);
            }
        };

        return Service;

    });
})();