/* Based on  php-cuong/magento-offline-payments  */
define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'pdqpayment',
            component: 'Bluem_Integration/js/view/payment/method-renderer/pdqpayment-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
