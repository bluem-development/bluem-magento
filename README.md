# bluem-magento
Bluem Magento via docker


Based on https://github.com/mageplaza/magento-2-module-creator/tree/master/sample-payment-method



## Installation

Install the module in a working **Magento 2.0** installation from its root folder by executing the following command:

```
composer require daanrijpkema/bluem-magento:dev-main
```

When you are asked for credentials, use a generated Access key from your magento.com account. Go to https://marketplace.magento.com/customer/accessKeys/ (manual : https://devdocs.magento.com/guides/v2.3/install-gde/prereq/connect-auth.html ) to create a public/private key pair and use that as username/password combination respectively.


```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```
# reference: https://magento.stackexchange.com/questions/90913/how-to-install-custom-extension-in-magento2
# example: https://www.mageplaza.com/install-magento-2-extension/#solution-1-ready-to-paste



##  Configuration

Now time to setup it in backend.

Go to Bluem > Payments > Configuration.
