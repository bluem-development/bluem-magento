<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;

// https://magento.stackexchange.com/questions/178873/magento-2-get-customer-data-after-login-with-observer

class UpdateUserBluemMeta implements ObserverInterface
{
    protected $_customerRepositoryInterface;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(Observer $observer)
    {
        $debug = false;

        // find requests that are tied to current IP But not to a user ID
        $customer = $observer->getEvent()->getCustomer();
        $requestModel = ObjectManager::getInstance()->create(
            'Bluem\Integration\Model\Request'
        );
        $remote = ObjectManager::getInstance()->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
        $cur_ip = $remote->getRemoteAddress();

        // improve filters to only search for non-user-based requests
        $collection = $requestModel->getCollection()->addFieldToFilter(
            "user_id",
            [
                'eq' => '0'
            ]
        );
        foreach ($collection as $c) {
            $d = $c->getData();

            // only consider completed requests
            // and only consider requests made by guest users
            if ($d['status']!== "response_success"
                || $d['user_id'] !== "0"
            ) {
                continue;
            }

            $pl = json_decode($d['payload']);
            if (isset($pl->ip)
                && $cur_ip===$pl->ip
            ) {
                // if found connect it to the current user ID
                $new_user_id = $customer->getId();
                $c->setUserId($new_user_id);
                $c->save();

                if ($debug) {
                    var_dump("Found!");
                    var_dump($d);
                    var_dump("Connecting to userid; ". $new_user_id." result: ");
                }
            }
        }
    }
}
