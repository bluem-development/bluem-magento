define([
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
], function (ko, Component, _, stepNavigator, customer, customerData, checkoutData) {
    'use strict';

    /**
     * idincheck - is the name of the component's .html template,
     * Bluem_Integration  - is the name of your module directory.
     */
    return Component.extend({
        defaults: {
            template: 'Bluem_Integration/idincheck'
        },

        isVisible: ko.observable(false),
        
        isVerified: ko.observable(false),
        
        isLoggedIn: customer.isLoggedIn(),
        
        // Step code will be used as step content id in the component template
        stepCode: 'identification',
        
        // Step title value
        stepTitle: 'Identification',
        
        /**
         * Check if a verification is required based on products in cart.
         */
        requireVerification: function () {
            let cart_details = customerData.get('cart')();
            let cart_items = cart_details.items;
            
            if (cart_items.length > 0) {
                for (var i=0; i<cart_items.length; i++) {
                    if (cart_items[i].require_age_verification == 'yes') {
                        return true;
                    }
                }
            }
            return false;
        },
        
        /**
         * Check if verification is done.
         */
        verificationDone: function () {
            let sections = ['cart','customer'];
            
            customerData.invalidate(sections);
            customerData.reload(sections, true);
            
            let cart_details = customerData.get('cart')();
            let customer_details = customerData.get('customer')();
            
            if (customer_details.identity_valid == true) {
                return true;
            } else if (cart_details.is_bluem_verified == 'yes') {
                return true;
            }
            return false;
        },

        /**
         * @returns {*}
         */
        initialize: function () {
            this._super();
            
            // Check if verification is previously done
            if (this.verificationDone()) {
                this.isVerified(true);
            }
            
            let cart_details = customerData.get('cart')();
            
            // Check if age verification is enabled
            if (cart_details.age_verification_enabled == 'yes') {
                // Check if the check is required by conditions
                if (this.requireVerification() == true) {
                    stepNavigator.registerStep(
                        this.stepCode,
                        // Step alias
                        null,
                        this.stepTitle,
                        // Observable property with logic when display step or hide step
                        this.isVisible,

                        _.bind(this.navigate, this),

                        /**
                         * Sort order value
                         * 'sort order value' < 10: step displays before shipping step;
                         * 10 < 'sort order value' < 20 : step displays between shipping and payment step
                         * 'sort order value' > 20 : step displays after payment step
                         */
                        1
                    );
                } else {
                    stepNavigator.next();
                }
            } else {
                stepNavigator.next();
            }
            return this;
        },

        /**
         * The navigate() method is responsible for navigation between checkout steps
         * during checkout. You can add custom logic, for example some conditions
         * for switching to your custom step
         * When the user navigates to the custom step via url anchor or back button we_must show step manually here
         */
        navigate: function () {
            customerData.reload(['cart'], true);
            
            let cart_details = customerData.get('cart')();
            
            // Check if age verification is enabled
            if (cart_details.age_verification_enabled == 'yes') {
                // Check if the check is required by conditions
                if (this.requireVerification() == true) {
                    this.isVisible(true);
                } else {
                    stepNavigator.next();
                }
            } else {
                stepNavigator.next();
            }
        },
        
        /**
         * Start the verification.
         */
        startVerification: function () {
            window.location.href = '/bluem/identity/request?returnurl=' + window.location.href;
        },

        /**
         * @returns void
         */
        navigateToNextStep: function () {
            customerData.reload(['cart'], true);
            
            let cart_details = customerData.get('cart')();
            
            // Check if age verification is enabled
            if (cart_details.age_verification_enabled == 'yes') {
                // Check if the check is required by conditions
                if (this.requireVerification() == true) {
                    // Check if the verification is done and valid
                    if (this.verificationDone() == true) {
                        stepNavigator.next();
                    } else {
                        this.startVerification();
                    }
                } else {
                    stepNavigator.next();
                }
            } else {
                stepNavigator.next();
            }
        }
    });
});
