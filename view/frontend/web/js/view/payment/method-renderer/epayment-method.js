/* Based on  php-cuong/magento-offline-payments  */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url',
    'jquery',
    'mage/validation'
], function (Component, additionalValidators, redirectOnSuccessAction, urlBuilder, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bluem_Integration/payment/epayment-form',
            assistantId: ''
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('assistantId');

            return this;
        },

        /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                'additional_data': {
                    'assistant_id': typeof this.assistantId() !== "undefined" ? this.assistantId() : ""
                }
            };
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=epayment-form]';

            return $(form).validation() && $(form).validation('isValid');
        },

        
        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            console.log("PLACING ORDER VIA BLUEM");

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                            self.isPlaceOrderActionAllowed(true);
                    })
                    .done(function(orderID) {
                        console.log('Order placed, with ID ',orderID)
                        console.log("Lets go pay!")
                        $.ajax({
                            url: urlBuilder.build('bluem/payment/create'),
                            data: {'order_id': orderID},
                            dataType: 'json',
                            type: 'POST'
                        }).done(function (response) {
                            console.log("Response from Payment controller")
                            console.log(response)'
                            '
                            if (!response.error) {
                                window.location.replace(response.payment_url);
                                self.redirectAfterPlaceOrder = false;
                            } else {
                                redirectOnSuccessAction.execute();
                            }
                        }).fail(function (response) {
                            console.log(response);
                            redirectOnSuccessAction.execute();
                        });

                        self.afterPlaceOrder();
                    });
                return true;
            }

        }
    });
});
