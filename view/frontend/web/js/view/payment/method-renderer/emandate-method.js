/* Based on  php-cuong/magento-offline-payments  */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'mage/url',
    'jquery',
    // 'mage/validation',
    // 'Magento_Checkout/js/checkout-data',
    // 'Magento_Customer/js/model/customer'
], function (
    Component,
    additionalValidators,
    redirectOnSuccessAction,
    urlBuilder,
    $,
    // validator,
    // checkoutData,
    // customer
) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'Bluem_Integration/payment/emandate-form',
            issuer: ''
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('issuer');

            return this;
        },

        /**
        * @return {Object}
        */
        getData: function () {
            return {
                method: this.item.method,
                'additional_data': {
                    'issuer': typeof this.issuer() !== "undefined" ? this.issuer() : ""
                }
            };
        },

        /**
        * @return {jQuery}
        */
        validate: function () {
            var form = 'form[data-role=emandate-form]';

            return $(form).validation() && $(form).validation('isValid');
        },
        
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                        console.log("Failed placing order")
                        self.isPlaceOrderActionAllowed(true);
                    })
                    .done(function () {
                        console.log("Succeeded in placing order")
                        $.ajax({
                            url: urlBuilder.build('bluem/mandate/request'),
                            data: {},
                            dataType: 'json',
                            type: 'POST'
                        }).done(function (response) {
                            console.log("Successful AJAX response")
                            console.log(response);
                            alert(response);
                            if (!response.error) {
                                window.location.replace(response.payment_url);
                            } else {
                                redirectOnSuccessAction.execute();
                            }
                        }).fail(function (response) {
                            console.log("Failed AJAX")
                            console.log(response);
                            alert(response);
                            redirectOnSuccessAction.execute();
                        });
                        console.log("Done here.")
                        self.afterPlaceOrder();
                    }
                );
                return true;
            }
            return false;
        }
    });
});

/* PLACE ORDER SEMI WORKING */
/*
            
    placeOrder: function (data, event) {
        console.log("Placing order")
        var self = this;
        if (this.validate() && additionalValidators.validate()) {
            this.isPlaceOrderActionAllowed(false);

            this.getPlaceOrderDeferredObject()
                .always(() => {
                        self.afterPlaceOrder();

                        if (self.redirectAfterPlaceOrder) {
                            redirectOnSuccessAction.execute();
                        }
                    }
                );

            return true;
        }
    },
    beforePlaceOrder: function () {
        console.log('beforePlaceOrder')
        return true;
    },
    afterPlaceOrder: function () {
        this._super();

        console.log('afterPlaceOrder')
        console.log("Lets go pay!")
        $.ajax({
            url: urlBuilder.build('bluem/payment/request'),
            data: {},
            dataType: 'json',
            type: 'POST'
        }).done(function (response) {
            console.log("Response from Payment controller")
            console.log(response);
            console.log('-');
            if (!response.error) {
                window.location.replace(response.payment_url);
                self.redirectAfterPlaceOrder = false;
                redirectOnSuccessAction.execute();
            } else {
                
            console.error("ERROR");
            console.log(response);
            this.isPlaceOrderActionAllowed(true);
            }
        }).fail(function (response) {
            console.error("ERROR");
            console.log(response);
            this.isPlaceOrderActionAllowed(true);
            // return;
        });
        // window.location = urlBuilder.build('bluem/payment/request');
        // redirectOnSuccessAction.execute();
    }

    */
    /**
    * Place order. OLD AND NOT WORKING
    * 
    */

    // placeOrder: function (data, event) {
    //     var self = this;

    //     if (event) {
    //         event.preventDefault();
    //     }
    //     console.log("PLACING ORDER VIA BLUEM");

    //     if (this.validate() && additionalValidators.validate()) {
    //         this.isPlaceOrderActionAllowed(false);

    //         this.getPlaceOrderDeferredObject()
    //             .fail(function () {
    //                     self.isPlaceOrderActionAllowed(true);
    //             })
    //             .done(function(orderID) {
    //                 console.log('Order placed, with ID ','')
    //                 console.log("Lets go pay!")
    //                 $.ajax({
    //                     url: urlBuilder.build('bluem/payment/request'),
    //                     data: {'order_id': ''},
    //                     dataType: 'json',
    //                     type: 'POST'
    //                 }).done(function (response) {
    //                     console.log("Response from Payment controller")
    //                     console.log(response);
    //                     if (!response.error) {
    //                         window.location.replace(response.payment_url);
    //                         self.redirectAfterPlaceOrder = false;
    //                     } else {
    //                         redirectOnSuccessAction.execute();
    //                     }
    //                 }).fail(function (response) {
    //                     console.log(response);
    //                     redirectOnSuccessAction.execute();
    //                 });

    //                 self.afterPlaceOrder();
    //         });
    //         return true;
    //     }

    // }