# Bluem Magento module

## Requirements

- Magento2.* (tested on v2.4)

## Support

If you have any questions concerning the installation or configuration of the plug-in, or feature requests: Please contact us at [d.rijpkema@bluem.nl](mailto:d.rijpkema@bluem.nl?subject=Bluem+Magento2+Question). 

If you have any questions related to your account or your credentials, please refer to your Bluem account manager.

## License

GNU GPLv3.

## Installation:

### 1: Installing the module files

**Recommended way:** Install via Composer from your Magento Root:

```bash
composer require daanrijpkema/bluem-magento
```
*Manual (Advanced) installation*: Install the module code to `YOUR_MAGENTO_DIR/code/Bluem/Identity` and run `composer update` from within this folder. Be sure that you know how to configure and use this method.

### 2: Enable the module
Check to see if you can see the plug-in:
```bash
php bin/magento module:status
```
Enable the module as such:
```bash
php bin/magento module:enable Bluem_Integration
```

### 3: Update your Magento2 installation

This command is vital to ensure the proper instantiation of tables:

```
php bin/magento setup:upgrade
```

Run this in **production** mode or if you notice that the layout of your webshop is broken:
```
php bin/magento setup:static-content:deploy
```

## Updating this plugin

Periodically we will improve this plugin. Incorporating these brand new updates is easy:

Run:
```bash
composer require daanrijpkema/bluem-magento
```
and repeat step 2 and 3 from the aforementioned installation.


# Configuration of Bluem settings
Go to Stores > Configuration and open the Bluem Tab. 
Fill in all settings there - appropriate instructions are available on the page and through your account manager.

# Payments
This module offers a payment gateway that can be enabled to work inside the normal checkout flow of your webshop. 

To enable the payment gateway, go to Stores > Configuration, select Sales > Payment methods and scroll down. You should see a Bluem ePayment gateway there, which you can Enable. You might have to disable System setting; the small checkbox to the right.

**Important**: Also set your account settings and the Payment brandID by going to Bluem > Configure Account & Identity and scroll down to verify everything is filled in.

Once enabled within your webshop settings, end users can select the Bluem payment method. Once checkout has been initiated, a payment request will be created based on your Bluem settings and account credentials and the flow brings the end user to the Bluem payment page which, in turn, directs the user to their bank and consequently back to your webshop with a response.

In the administrator side, navigating to Bluem > Payments will yield an overview of all payment requests thus far, their status and their corresponding orders. Order status will also be automatically updated when customers pay.

_note_: Payments work out of the box for logged-in and guest customers

# Identity 
## How Identity Works
This module enables a page that is usable to verify **logged in user identities**. See below how this process works:

- _Note:_ Please make sure the settings (see Configuration section) for Identity are configured properly. One important setting to focus on is the type of request you want to initiate: either an AgeCheck Request or a CustomerData Request.

## Enabling automatic identity checking

Enforce an identity check before **logged in** users can add products to cart by selecting a desired scenario from the Bluem configuration page. Options are:

- Not performing an automatic identity check
- Requiring an age validation, based on the minimum age requirement set up (default 18 years and up).
- Requiring a regular identity verification to be completed 
- Requiring a regular identity verification to be completed and requiring a minimum age (default 18 years and up)

## Performing ad-hoc identity requests

- _Note:_ Replace `myawesome.shop` with your own store domain in the following steps.

1. Direct the user to the following page in your webshop:
```
https://myawesome.shop/bluem/identity/index
```

- If not verified, the user can initiate the process of verification
- The user will see their verification status there, if already verified.
- If not logged in, the user will see a prompt that they can only identify when logged in.

2. When initiated they are automatically redirected to a new page where a request is created.
You can also send them directly to this page to iniate a request (if applicable):
```
https://myawesome.shop/bluem/identity/request
```
3. The user will be redirect to Bluem portal for identity verification.
4. Afterwards, the user gets redirected back to the website and the identification status is  be presented. 
5. The user can click a button to return to the original page (step 1).

### Notes on Identity verification:

_Instructions on how to include the Identify button will follow soon_ 
The simplest way to do so is simply include a link to the page from step 2 of the above set-up procedure. When unverified users are redirected there, they can follow the procedure right away. If they are already verified, they will simply be told so and can return to the previous page

_Instructions on how to change the redirect after identification will follow soon_ Refer to the Configuration section to enable this functionality.

## Webhooks
Webhooks are vital to retrieve information about transactions in asynchronously and periodically about order processing, independent of your end-users explicit transaction and request page visit.

Webhooks will be developed in the near future and will be present here:

- https://myawesome.shop/bluem/payment/webhook (status: in development)
- https://myawesome.shop/bluem/identity/webhook (status: in development)
- https://myawesome.shop/bluem/mandate/webhook (status: in development)

They will function in the environment set in settings (either `test` or `prod`). See the Configuration section

As soon as the module webhook functionality has been developed, you can communicate this fact and the above URLs to your Bluem account manager to have the webhook communication enabled for your account.


## Notes
- IP Addresses are used at the moment to trace guest users. The IP Address is stored when a transaction is started. Please take notice of this when using this module within your site and acknowledge this within your privacy policy.

## Changelog

0.4.6   Working payment method

0.3.4   Initial release (Identity functions) to first participant testing.


### References:

Referred quite often to: https://www.mageplaza.com/magento-2-module-development/ and the official devdocs

<!-- 
# note to self, deployment:
zip -r bluem-integration-0.4.0.zip . -x '.git/*' -->

 