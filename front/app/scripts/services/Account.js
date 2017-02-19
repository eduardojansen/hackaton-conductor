(function () {
    'use strict';
    angular.module('inspinia').factory('Account', function ($http, APP_CONFIG) {

        var Service = {
            bancos: function() {
                return $http.get('/bancos.json');
            },
            add: function(data) {
                return $http.post(APP_CONFIG.baseUrl + '/rest/contas', data);
            }
        };

        return Service;

    });
})();