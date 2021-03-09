# bluem-magento
Bluem Magento via docker


Based on https://github.com/mageplaza/magento-2-module-creator/tree/master/sample-payment-method



## Installation



# reference: https://magento.stackexchange.com/questions/90913/how-to-install-custom-extension-in-magento2
# example: https://www.mageplaza.com/install-magento-2-extension/#solution-1-ready-to-paste






### Setup steps

http://localhost/setup

1. Readiness check: just click next
2. Setting up database: username, pass etc is all magento, host is db
3. Choose an admin url and remember it: localhost/admin_194six
4. Admin account: 
username: magento
email: daan.rijpkema.design@gmail.com
password: magent0!

5. click install

Your Store Address:
    http://localhost/ 
Magento Admin Address:
    http://localhost/admin_194six/ 
Be sure to bookmark your unique URL and record it offline.
Encryption Key: 743f93a6c171b88c6f1b09ae594fc813

For security, remove write permissions from these directories: '/var/www/html/app/etc'


6. attach to docker shell and Install the module in **Magento 2.0** installation from its root folder by executing the following command:

```
composer require daanrijpkema/bluem-magento:dev-main
```
(version to be changed to a stable tag)


When you are asked for credentials, use a generated Access key from your magento.com account. Go to https://marketplace.magento.com/customer/accessKeys/ (manual : https://devdocs.magento.com/guides/v2.3/install-gde/prereq/connect-auth.html ) to create a public/private key pair and use that as username/password combination respectively.

7. Then upgrade your magento installation to include the module, again from the magento root folder:

```
php ./bin/magento setup:upgrade
php ./bin/magento setup:static-content:deploy
```



installing our own package, 
first include additional dependencies;
composer require magento/magento-composer-installer

then we can add?







##  Configuration

Now time to setup it in backend.

Go to Bluem > Payments > Configuration.



