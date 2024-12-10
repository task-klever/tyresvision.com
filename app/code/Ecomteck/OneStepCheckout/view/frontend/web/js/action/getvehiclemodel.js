/*global define,alert*/
define(
        [
            'ko',
            'jquery',
            'mage/storage',
            'mage/translate',
            'Magento_Checkout/js/model/step-navigator',
        ],
        function (
                ko,
                $,
                storage,
                $t,
                stepNavigator
                ) {
            'use strict';
            return function (area, responce) {
                $('body').trigger('processStart');
                return storage.post(
                        'shippingform/ajax/getvehiclemodel',
                        JSON.stringify(area),
                        false
                        ).done(
                        function (response) {
                            if (response) {
                                $.each(response, function (i, v) {         
                                        $("select[name='vehiclemodel1']").html(v.vehiclemodel);
                                        $("select[name='vehicleyear1']").html(v.vehicleyear);
                                });
                            }
                            $('body').trigger('processStop');
                        }

                ).fail(
                        function (response) {
                        }
                );
            };
        }
);