var config = {
    'config': {
        'mixins': {
           'Magento_Checkout/js/view/shipping': {
               'Bluem_Integration/js/view/shipping-payment-mixin': true
           },
           'Magento_Checkout/js/view/payment': {
               'Bluem_Integration/js/view/shipping-payment-mixin': true
           }
       }
    }
}
