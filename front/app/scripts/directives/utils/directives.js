function loadingSpinner($http) {
    return {
        templateUrl: 'scripts/directives/utils/loading-spinner.html?v='+version,
        restrict: 'E',
        replace: true,
        link: function (scope, elm, attrs) {

            scope.isLoading = function () {
                return $http.pendingRequests.length > 0;
            };

            scope.$watch(scope.isLoading, function (v) {
                if (v) {
                    elm.show();
                } else {
                    elm.hide();
                }
            });
        }
    };

};

function setIcon($compile) {

    var getTemplate = function (status) {
        var template = '';

        switch (status) {
            case 'bar':
                template = 'fa fa-glass';
                break;
            case 'café':
                template = 'fa fa-coffee';
                break;
            case 'restaurante':
                template = 'fa fa-cutlery';
                break;
            default:
                template = 'fa fa-place';
                break;
        };

        return template;
    }

    return {
        restrict: 'A',
        replace: true,
        scope: {
            item: '='
        },
        link: function (scope, elem, attr) {

            elem.addClass(getTemplate(scope.item));

            $compile(elem.contents())(scope);

        }
    };
}

function emailValid($http, APP_CONFIG) {
    return {
        require: 'ngModel',
        link: function (scope, ele, attrs, c) {
            ele.bind('blur', function () {
                var atts = attrs.ngModel.split('.');
                var att = scope;

                atts.forEach(function (entry) {
                    att = att[entry]
                });

                var url = APP_CONFIG.baseUrl + '/validator/check-user-exists?email=' + att;
                if (angular.isDefined(scope.row.codigo) && atts[1] == 'email') {
                    url += '&codigo=' + scope.row.codigo;
                }

                if (att) {
                    $http.get(url).then(function (data) {
                        c.$setValidity('cvalid', true);
                    }, function (error) {
                        c.$setValidity('cvalid', false);
                    });

                } else {
                    c.$setValidity('cvalid', true);
                }

            });
        }
    }
}

function clearString($compile) {

    var map = {"â": "a", "Â": "A", "à": "a", "À": "A", "á": "a", "Á": "A", "ã": "a", "Ã": "A",
        "ê": "e", "Ê": "E", "è": "e", "È": "E", "é": "e", "É": "E",
        "î": "i", "Î": "I", "ì": "i", "Ì": "I", "í": "i", "Í": "I",
        "õ": "o", "Õ": "O", "ô": "o", "Ô": "O", "ò": "o", "Ò": "O", "ó": "o", "Ó": "O",
        "ü": "u", "Ü": "U", "û": "u", "Û": "U", "ú": "u", "Ú": "U", "ù": "u", "Ù": "U",
        "ç": "c", "Ç": "C",
        "-": " ",
        " ": " "};

    function removerAcentos(s) {

        return s.replace(/[\W\[\] ]/g, function (a) {

            if (a == ' ') {
                return ' ';
            }

            return map[a] || a;
        });
    };

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function (scope, elem, attr, ngModel) {

            elem.bind('keyup', function () {
                var vValue = ngModel.$viewValue;
                var newValue = removerAcentos(vValue);


                scope.$apply(function () {
                    ngModel.$setViewValue(newValue);
                });

                elem.val(ngModel.$viewValue);
            });

        }
    };
}

function onlyDigits() {

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function (scope, element, attrs, modelCtrl) {
            modelCtrl.$parsers.push(function (inputValue) {
                if (inputValue == undefined)
                    return '';
                var transformedInput = inputValue.replace(/[^0-9]/g, '');
                if (transformedInput !== inputValue) {
                    modelCtrl.$setViewValue(transformedInput);
                    modelCtrl.$render();
                }
                return transformedInput;
            });
        }
    };
}

function onlyLetters() {

    return {
        restrict: 'A',
        require: 'ngModel',
        link: function (scope, element, attrs, modelCtrl) {
            modelCtrl.$parsers.push(function (inputValue) {
                if (inputValue == undefined)
                    return '';
                var transformedInput = inputValue.replace(/[^A-Za-z]/g, '');
                if (transformedInput !== inputValue) {
                    modelCtrl.$setViewValue(transformedInput);
                    modelCtrl.$render();
                }
                return transformedInput;
            });
        }
    };
}

function maskMoney($timeout, $locale) {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            model: '=ngModel',
            mmOptions: '=?',
            prefix: '=',
            suffix: '=',
            affixesStay: '=',
            thousands: '=',
            decimal: '=',
            precisoin: '=',
            allowZero: '=',
            allowNegative: '='
        },
        link: function (scope, el, attr, ctrl) {

            scope.$watch(checkOptions, init, true);

            scope.$watch(attr.ngModel, eventHandler, true);
            //el.on('keyup', eventHandler); //change to $watch or $observe

            function checkOptions() {
                return scope.mmOptions;
            }

            function checkModel() {
                return scope.model;
            }



            //this parser will unformat the string for the model behid the scenes
            function parser() {
                return $(el).maskMoney('unmasked')[0]
            }
            ctrl.$parsers.push(parser);

            ctrl.$formatters.push(function (value) {
                $timeout(function () {
                    init();
                });
                return parseFloat(value).toFixed(2);
            });

            function eventHandler() {
                $timeout(function () {
                    scope.$apply(function () {
                        ctrl.$setViewValue($(el).val());
                    });
                })
            }

            function init(options) {

                $timeout(function () {
                    elOptions = {
                        prefix: scope.prefix || '',
                        suffix: scope.suffix,
                        affixesStay: scope.affixesStay,
                        thousands: scope.thousands || ".",
                        decimal: scope.decimal || ",",
                        precision: scope.precision,
                        allowZero: scope.allowZero,
                        allowNegative: scope.allowNegative
                    }

                    if (!scope.mmOptions) {
                        scope.mmOptions = {};
                    }

                    for (elOption in elOptions) {
                        if (elOptions[elOption]) {
                            scope.mmOptions[elOption] = elOptions[elOption];
                        }
                    }

                    $(el).maskMoney(scope.mmOptions);
                    $(el).maskMoney('mask');
                    eventHandler()

                }, 0);

                $timeout(function () {
                    scope.$apply(function () {
                        ctrl.$setViewValue($(el).val());
                    });
                })

            }
        }
    }
}

angular.module('inspinia')
    .directive('loadingSpinner', loadingSpinner)
    .directive('setIcon', setIcon)
    .directive('emailValid', emailValid)
    .directive('clearString', clearString)
    .directive('onlyDigits', onlyDigits)
    .directive('onlyLetters', onlyLetters)
    .directive('maskMoney', maskMoney)
    .filter('clearstring', function() {
        return function(item) {
            return item.replace(/[^\d]+/g,'');
        }
    });