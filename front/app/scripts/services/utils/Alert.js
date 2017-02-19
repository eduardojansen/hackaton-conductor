(function () {
    'use strict';
    angular.module('inspinia').service('Alert', function (SweetAlert) {

        var Alert = {
            error: function (title, text, callback) {

                var _title = title || 'Oops!';
                var _text = text || 'Ocorreu um erro';

                SweetAlert.swal({
                    title: _title,
                    text: _text,
                    type: 'error',
                    timer: 5000
                }, function () {

                    if (angular.isDefined(callback)) {
                        callback();
                    }

                });

            },
            warning: function (title, text, callback) {

                var _title = title || 'Oops!';
                var _text = text || 'Ocorreu um erro';

                SweetAlert.swal({
                    title: _title,
                    text: _text,
                    type: 'warning',
                    timer: 5000
                }, function () {

                    if (angular.isDefined(callback)) {
                        callback();
                    }

                });

            },
            success: function (title, text, callback) {

                var _title = title || 'Sucesso!!';
                var _text = text || 'Operação efetuada com sucesso.';

                SweetAlert.swal({
                    title: _title,
                    text: _text,
                    type: "success",
                    timer: 5000
                }, function () {

                    if (angular.isDefined(callback)) {
                        callback();
                    }

                });
            },
            confirm: function (title, text, confirmButton, callback, cancelButton) {

                var _title = title || 'Você tem certeza?';
                var _text = text || 'Você não poderá mais recuperar este registro.';
                var _confirmButton = confirmButton || {color: '#85b700', text: 'Sim, tenho certeza'};
                var _cancelButton = cancelButton || 'Não';

                SweetAlert.swal({
                    html: true,
                    title: _title,
                    text: _text,
                    type: "warning",
                    showCancelButton: true,
                    cancelButtonText: _cancelButton,
                    confirmButtonColor: _confirmButton.color,
                    confirmButtonText: _confirmButton.text,
                    closeOnConfirm: true,
                    closeOnCancel: true,
                    showLoaderOnConfirm: true
                }, function (isConfirm) {

                    if (angular.isDefined(callback)) {

                        if (angular.isDefined(callback.success) || angular.isDefined(callback.error)) {

                            if (isConfirm) {
                                callback.success();
                            } else {
                                callback.error();
                            }

                        } else {
                            if (isConfirm) {
                                callback();
                            }
                        }


                    }

                });

            }
        };

        return Alert;

    });
})();