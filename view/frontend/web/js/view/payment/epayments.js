/* Based on  php-cuong/magento-offline-payments  */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'emandate',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-method'
        },
        {
            type: 'epayment',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-bank-method'
        },
        {
            type: 'epayment_paypal',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-method'
        },
        {
            type: 'epayment_creditcard',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-method'
        },
        {
            type: 'epayment_cartebancaire',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-method'
        },
        {
            type: 'epayment_sofort',
            component: 'Bluem_Integration/js/view/payment/method-renderer/epayment-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
