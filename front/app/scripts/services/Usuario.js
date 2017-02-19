(function () {
    'use strict';
    angular.module('inspinia').factory('Usuario', function ($http, APP_CONFIG) {

        var Service = {
            get: function(id) {
                var id = id || '';

                var url = APP_CONFIG.baseUrl + '/rest/usuarios/';

                if (id) {
                    url += id;
                }

                return $http.get(url);
            },
            update: function(id, data) {

                var id = id || '';

                var url = APP_CONFIG.baseUrl + '/rest/usuarios/';

                if (id) {
                    url += id;
                }

                return $http.put(url, data);
            },
            friends: function(data) {
                return $http.post(APP_CONFIG.baseUrl + '/rest/usuarios', data);
            },
            updateTransations: function() {
                return $http.get(APP_CONFIG.baseUrl + '/rest/usuarios/minhas-transacoes');
            },
            sugestoes: function(data) {
                return $http.post(APP_CONFIG.baseUrl + '/rest/usuarios/sugestoes', data);
            }
        };

        return Service;

    });
})();