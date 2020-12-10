# bluem-magento
Bluem Magento via docker


## Installation

Install the module in a working **Magento 2.0** installation from its root folder by executing the following command:

```
composer require daanrijpkema/bluem-magento:dev-main
```

When you are asked for credentials, use a generated Access key from your magento.com account. Go to https://marketplace.magento.com/customer/accessKeys/ to create a public/private key pair and use that as username/password combination respectively.


```
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```


# example: https://www.mageplaza.com/install-magento-2-extension/#solution-1-ready-to-paste