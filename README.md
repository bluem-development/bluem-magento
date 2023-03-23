![Bluem](https://bluem.nl/img/BluemAboutIcon.svg)

Bluem is an independent Dutch payment and identity specialist based in Amersfoort and Sofia, which has developed its viamijnbank cloud platform for handling online payments, mandates, identification services, digital signatures and IBAN name checks. Companies send high volumes of payment requests through our software, bills are paid faster, and direct debit mandates are issued digitally.

This Magento2 module is an integration that connects your website to Bluem's eMandate, ePayments and iDIN Identity services.

Concretely, the module delivers:

- A custom plug and play page allows logged-in users to perform an iDIN Identity request and store it within the user profile metadata for further usage in third-party modules or functions within your webshop. 
- An extensive settings page that allows for enabling/disabling and configuration of specific services
- A payment gateway to accept eMandate transactions within a Magento2 checkout procedure (in development)
- A payment gateway to accept ePayment transactions within a Magento2 checkout procedure
- A view allows logged-in users to perform an eMandate transaction request and record the response within the user profile metadata.
- and more to come!

## Support

If you have any questions concerning the module's installation or configuration or feature requests, please contact us at [pluginsupport@bluem.nl](mailto:pluginsupport@bluem.nl?subject=Bluem+Magento2+Question).

If you have any questions related to your account or your credentials, please refer to your Bluem account manager.

## Requirements

- Magento2.* (tested on v2.4.5)

## License

GNU GPLv3.

## Installation:

### 1: Installing the module files

**Recommended way:** Install via Composer from your Magento Root:

_ Notice:_ This method is still undergoing some configuration changes, so if it does not work at the moment, please install the module manually using the steps in the section mentioned below.

```bash
composer require bluem-development/bluem-magento
```


You require authorization keys from Magento's module repository. You can find these in your Magento account. [Refer to this help article to find your auth credentials](https://devdocs.magento.com/guides/v2.4/install-gde/prereq/connect-auth.html). [Here, you can find a guide on how to install and update modules through composer](https://devdocs.magento.com/cloud/howtos/install-components.html).

_ Notice_: This is the developer repository for the Bluem Magento2 module. Use the code in this repository to get insight and possibly contribute to the development of this module. A subset of this documentation will be available as end-user documentation in the future as well. Only if you are familiar with Magento will you be able to properly install the module from the pure source code.

**Manual (Advanced) installation** 
- Either download the provided ZIP file of the latest version from the root directory or clone/fork the source code.  
- Install the module code to `YOUR_MAGENTO_DIR/code/Bluem/Integration` and run `composer update` from within this folder. Be sure that you know how to configure and use this method.

**Magento Marketplace**: will be considered at a later stage of development.


### 2: Enable the module
Check to see if you can see the module in the response of this command:
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

Please test your module and its functionality thoroughly, as this module is still in very active development, and significant releases will still follow.

_ Notice:_ Run this in **production** mode or if you notice that the layout of your webshop is broken:
```
php bin/magento setup:static-content:deploy
```

## Updating to newer versions 

Periodically we will significantly improve this module. Incorporating these brand new updates is easy:

Run:
```bash
composer require bluem-development/bluem-magento
```
Then repeat step 2 and 3 from the aforementioned installation.


# Configuration of Bluem settings
Go to `Stores` > `Configuration` and open the Bluem Tab. 
Fill in all settings there - appropriate instructions are available on the page and through your account manager.

Detailed instructions per service are stated below:

# Payments
This module offers a payment gateway that functions seamlessly inside the checkout flow of your webshop. 

## Enabling and configuring payments

To enable the payment gateway, go to Stores > Configuration, select Sales > Payment methods. You should see a Bluem ePayment gateway there, which you can Enable. *Note:* You might have to disable the System setting, the small checkbox to the right, to allow changes to specific settings.

**Important**: Set your account settings by going to Bluem > Configure Account & Identity and scroll down to verify the complete configuration. You have to fill in your account details such as your SenderID, the Payment brandID and the access token for the Test and or Production environment setting. It is strongly advised to first test all functionality in an isolated test instance of your website and use the Test environment setting of this module.

If end users encounter errors _within_ the Bluem portal viamijnbank.net, this is probably because of an incorrectly configured or not yet activated account within the bank or a problem with your account credentials. Please check if your details are correctly filled in. Refer to your Bluem account manager if you are unsure.

## Using payments

Once enabled within your webshop settings, end users can select the Bluem payment method when they check out. 

Once the checkout is completed, a payment request is created based on your Bluem settings and account credentials. The flow brings the end-user to the Bluem payment page, which, in turn, directs the user to their bank and consequently back to your webshop with a response. A webhook (described below) transfers additional status information from Bluem to your website after the transaction has been completed or otherwise processed (failed, expired, etc.).

## Managing and viewing payments

On the administrator side, navigating to Bluem > Payments will yield an overview of all payment requests thus far, their status, and corresponding orders. The order status will also be automatically updated when users pay. In the payload section of this overview, you can view details of each transaction.

Also refer to the [Production viamijnbank.net](https://viamijnbank.net) or [Test viamijnbank.net](https://test.viamijnbank.net) portal. Bluem client can see more in-depth details of each transaction to troubleshoot or investigate your end users' payment habits. 

Payments work out of the box for logged-in and guest users. Payments work alongside and independently from identity. An identity can be configured to be required before the Checkout procedure can be completed. Only after this requirement is met, the checkout procedure and subsequent payment methods will be selectable.

# Identity 
## How Identity service Works
This module enables identification during checkout and account verification.

The Identity Requests page consists of an overview of the requests made so far, selectable from the Bluem menu in your website's admin side after activating the module. There is also an explanation of how to start the request procedure on the overview page.

On the Settings page, you can now find several settings around the items Account, Identification and Payments. Mainly elaborated is now account settings and settings for identity. See also the scenarios as they are in the [WordPress and WooCommerce plug-in](https://wordpress.org/plugins/bluem/). All these scenarios are available and explained below.

You can indicate in the settings which parts of an IDIN request are requested. CustomerID (an identifier set by the bank) is always requested. All these settings are already in active use and can therefore are present in this version.

- _Note:_ Please ensure the settings (see Configuration section) for the Identity service are correct. One important setting to focus on is the type of request you want to initiate: either an AgeCheck Request or a CustomerData Request.

## Choosing and enabling automatic identity-checking scenario's

Enforce an identity check during checkout by selecting a desired scenario from the Bluem configuration page. Options are:

- Not performing an automatic identity-check
- Requiring an age validation based on the minimum age requirement set up (default 18 years and up).
- Requiring a regular identity verification to be completed 
- Requiring a regular identity verification to be completed and requiring a minimum age (default 18 years and up)

## Or: perform ad-hoc identity requests

- _Note:_ Replace `myawesome.shop` with your store domain in the following steps.

1. Direct the user to the following page in your webshop:
```
https://myawesome.shop/bluem/identity/index
```

- If not verified, the user can initiate the process of verification
- The user will see their verification status there if they are already verified.
- If not logged in, the user will see a prompt that they can only identify when logged in.

2. When initiated, the user is redirected to a newly requested transaction, leading them to the Bluem portal for identity verification.

Additionally, you can also refer them directly to this page to initiate a request (if applicable):
```
https://myawesome.shop/bluem/identity/request
```
3. Afterwards, the user gets redirected back to the website, and the identification status is shown. 
4. The user can click a button to return to the original page (step 1).

## Ensure some products are set to require identity/age verification

Set a new product attribute at the products that require an age check to `1`. The key of this product attribute is set in the settings. If unchanged, the default key is `agecheck_required`. This attribute will be added automatically during install or update.

### Important notes on identity verification:

_Instructions on how to include the Identify button will follow soon_ 
The simplest way to do so is to include a link to the page from step 2 of the above set-up procedure. When unverified users are redirected there, they can follow the procedure right away. If they are already verified, they will be told so and can return to the previous page.

_Instructions on how to change the redirect after identification will follow soon._ Refer to the Configuration section to enable this functionality.
# eMandates

Will follow in a future release, as soon as Bluem clients have shown interest in integrating this into the Magento ecosystem.

# Development environment
To set up a dev-environment, we'll using Docker (docker.io) to run a project with containers to simulate a running Magento 2 instance locally. If you're not familiar with Docker, please read their documentation.
To install and run the instance, run the following command from 'docker' folder:
```
docker-compose up
```
'bluem-magento2-dev' is the name of the Docker container, which will be used in the next sections. Usely this is the name of the directory.

## Shell access
To access the shell, use the following command:
```
docker exec -it "bluem-magento2-dev" /bin/bash
```

## Magento installation directory
Magento is installed in the following directory (root):
```
/bitnami/magento
```

## Running Magento commands
To run Magento commands, use the following command:
```
docker exec -it "bluem-magento2-dev" /bin/bash php bin/magento list
```

## Clear cache, recompile
To easily clear all cache and recompile after changes on module code, we've created a shell script. This is recommended after  Run the following code from 'docker' folder:
```
sh reload-application.sh -p bluem-magento2-dev
```
'bluem-magento2-dev' is the name of the Docker container.

# Developer notes

- At the moment, it is not yet possible to choose the issuer (Bank) from within the webshop; this is done from within the Bluem portal, so as soon as the end-user has clicked on 'checkout' and temporarily leaves the webshop.
- At the moment, the payment reference and client reference are automatically generated based on client information. This will be added at a later date if it appears to be necessary.
- All transaction requests, also for identity, are logged within a database table which is created when the module is first activated.
- Notes on Magento version 2: This module is targeted at Magento2, and there is no support for Magento v1.* at the moment.
- Regarding data and privacy: IP Addresses are used at the moment to trace guest users. Any new transaction stores the IP Address. Please notice this when using this module within your site and acknowledge this within your privacy policy.
- Please contact us with any remarks or notes on the module and its installation procedure: the module is rather new and therefore not yet broadly tried and tested.

## Webhooks (in development)
Webhooks are vital to retrieve information about transactions asynchronously and periodically about order processing, independent of your end-users explicit transaction and request page visit.

Webhooks will be developed shortly and will be present here:

- https://myawesome.shop/bluem/payment/webhook (status: in development)
- https://myawesome.shop/bluem/identity/webhook (status: in development)
- https://myawesome.shop/bluem/mandate/webhook (status: in development)

They will function in the environment set in settings (either `test` or `prod`). See the Configuration section.

When completed, you can communicate this fact and the above URLs to your Bluem account manager to have the webhook functionality enabled for your account.


## Changelog
See also https://github.com/bluem-development/bluem-magento/releases

0.7.2   Some bugfixes and code improvement
0.7.1   Some bugfixes and code improvement
0.7.0   Magento v2.4.5 and PHP8+ support. Added additional payment methods
0.6.0   Improvements, identification service as step during checkout, account verification
0.5.12  Fixing composer dependency issues
0.5.11  Fixing composer dependency issues
0.5.10  Major updated to dependency libraries; improving blocking of products through checkout filter
0.5.9   Updated composer dependencies, fixing minor bugs
0.5.8   Domain whitelisting for iDIN
0.5.5   Improved identification service including robust guest identification and flow when registering or logging in
0.5.4   Code standardization, minor stability fixes
0.5.3   Minor changes
0.5.2   Updating Bluem dependencies to most recent version
0.5.1   Cleared out some artefact code calls, Improved code stability, added versatile product filters
0.4.7   Documentation updated
0.4.6   Working payment method - ready for first deployment and testing of use cases
0.3.4   Initial release (Identity functions) to first participant testing.
0.2.0   IDIN functional, settings page functional. Working on datastore and model creation

### References
I referred quite often to [MagePlaza](https://www.mageplaza.com/magento-2-module-development) and [the official dev docs](https://devdocs.magento.com/).

<!-- 
# note to self, deployment:
zip -r bluem-integration-0.4.0.zip . -x '.git/*' -->
