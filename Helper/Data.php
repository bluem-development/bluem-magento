<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\ObjectManager;

use stdClass;

class Data extends AbstractHelper
{
    protected $_customerSession;
    protected $_baseURL;

    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;

        $this->_storeManager = ObjectManager::getInstance()
            ->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();
    }
    const XML_BASE_PATH = 'integration';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigForSection($section, $code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_BASE_PATH . '/' . $section . '/'. $code,
            $storeId
        );
    }


    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("general", $code, $storeId);
    }

    public function getIdentityConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("identity", $code, $storeId);
    }

    public function getPaymentsConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("payments", $code, $storeId);
    }

    public function getIdentityValid()
    {
        $debug = false;

        $identity_scenario = (int) $this->getIdentityConfig('identity_scenario');
        $min_age = (int) $this->getIdentityConfig('identity_min_age');

        if ($debug) {
            echo "";
            echo "<br/>identity_scenario: " .
                print_r(
                    $identity_scenario,
                    true
                );
            echo "<br/>identity_min_age: " .
                print_r(
                    $this->getIdentityConfig('identity_min_age'),
                    true
                );
            echo "<br/>retrieving name? " .
                print_r(
                    $this->getIdentityConfig('identity_request_name'),
                    true
                );
            echo "<br/>retrieving address? " .
                print_r(
                    $this->getIdentityConfig('identity_request_address'),
                    true
                );
            echo "<br/>retrieving birthdate? " .
                print_r(
                    $this->getIdentityConfig('identity_request_birthdate'),
                    true
                );
            echo "<br/>retrieving agecheck? " .
                print_r(
                    $this->getIdentityConfig('identity_request_agecheck'),
                    true
                );
            echo "<br/>retrieving gender? " .
                print_r(
                    $this->getIdentityConfig('identity_request_gender'),
                    true
                );
            echo "<br/>retrieving telephone? " .
                print_r(
                    $this->getIdentityConfig('identity_request_telephone'),
                    true
                );
            echo "<br/>retrieving email? " .
                print_r(
                    $this->getIdentityConfig('identity_request_email'),
                    true
                );
            echo "<br/>identity_requests_parsed: " .
                print_r(
                    $this->getIdentityRequestCategories(),
                    true
                );
        }

        $valid = true; // valid until proven otherwise
        $invalid_message = "";

        $requestURL = "{$this->_baseURL}bluem/identity/request?goto=shop";

        // require any identity check?
        if ($identity_scenario >= 1) {
            if ($debug) {
                echo " There is some form of identity check required. Scenario: ";
                var_dump($identity_scenario);

                echo " - is customer logged in? ";
                echo ($this->_customerSession->isLoggedIn()?"yes":"no");
            }
            $identity_checked = $this->_customerSession->isLoggedIn() ?
                    $this->getBluemUserIdentified():
                    $this->getBluemGuestIdentified();

            if ($identity_checked->status == false) {
                $valid = false;
                $invalid_message = "<a
                    href='{$requestURL}'
                    target='_self'>You must first identify yourself</a>
                    before you can add products to the cart.";
            }
            if ($identity_scenario == 1) {
                // also require an age check
                if ($debug) {
                    echo " CUR REPORT: ";
                    var_dump($identity_checked->report);
                }

                if (isset($identity_checked->report->AgeCheckResponse)) {
                    if ($identity_checked->report->AgeCheckResponse."" == "true"
                        || $identity_checked->report->AgeCheckResponse."" == "1"
                    ) {
                        $valid = true;
                    } else {
                        $valid = false;
                        $invalid_message = "The reported age from your
                            identification is not sufficient.
                            <a href='{$requestURL}'
                            target='_self'>Please identify again</a> or contact us.";
                    }
                } else {
                    $valid = false;
                    $invalid_message = "You will have to verify your 
                        age for some products in the store. <a href='{$requestURL}'
                        target='_self'>Please verify your age by identifying</a>. 
                        It takes no more than one minute and
                        the result is stored for future transactions 
                        when you are logged in as a regular customer.";
                }
            }
            // definitely require any identification already
            if ($identity_scenario == 3) {
                // also require age check
                if (isset($identity_checked->report->BirthDateResponse)
                && ($identity_checked->report->BirthDateResponse."")!==""
                ) {

                    // calculate difference
                    $now_time = strtotime("now");

                    $then_time = strtotime(
                        $identity_checked->report->BirthDateResponse.""
                    );

                    $diff_sec = $now_time - $then_time;

                    $age_in_years = round($diff_sec / 60 / 60/ 24 / 365, 0);

                    if ($debug) {
                        // print_r($identity_checked->report);
                        // var_dump($identity_checked->report->BirthDateResponse."");

                        // echo "<br>now_time = {$now_time}";
                        // echo "<br>then_time = {$then_time}";
                        // echo "<br>diff_sec = {$diff_sec}";
                        echo "<br>age_in_years = {$age_in_years}";
                    }

                    if ($age_in_years < $min_age) {
                        $valid = false;
                        $invalid_message = "Your age appears to be insufficient.
                        The minimum age of <strong>{$min_age} years</strong>
                        is required. Contact us if you have any questions.";
                    } else {
                        $valid = true;
                    }
                } else {
                    $valid = false;
                    $invalid_message = "We could not verify your age.
                    <a href='{$requestURL}'
                    target='_self'>Please identify again</a>
                    or contact us if you have any questions";
                }
            }
        }

        $explanation_html = "";
        if (!$valid) {
            $explanation_html = "<br><small>
            <a href='{$this->_baseURL}bluem/identity/information' target='_blank' title='Learn more about iDIN - identity validation'>
            What is this?</a></small>";
        }

        return (object)[
            'valid'=>$valid,
            'invalid_message'=>$invalid_message . $explanation_html
        ];
    }

    public function getIdentityRequestCategories()
    {
        $scenario = (int) $this->getIdentityConfig('identity_scenario');

        if ($scenario == 1) {
            return ["AgeCheckRequest"];
        }

        // always add this when checking generic identity
        $requestCategories = [
            "CustomerIDRequest"
        ];

        $identity_request_name = $this->getIdentityConfig(
            'identity_request_name'
        );
        if ($identity_request_name=="1") {
            $requestCategories[] = "NameRequest";
        }
        $identity_request_address = $this->getIdentityConfig(
            'identity_request_address'
        );
        if ($identity_request_address=="1") {
            $requestCategories[] = "AddressRequest";
        }
        $identity_request_birthdate = $this->getIdentityConfig(
            'identity_request_birthdate'
        );
        if ($identity_request_birthdate=="1") {
            $requestCategories[] = "BirthDateRequest";
        }
        $identity_request_gender = $this->getIdentityConfig(
            'identity_request_gender'
        );
        if ($identity_request_gender=="1") {
            $requestCategories[] = "GenderRequest";
        }
        $identity_request_telephone = $this->getIdentityConfig(
            'identity_request_telephone'
        );
        if ($identity_request_telephone=="1") {
            $requestCategories[] = "TelephoneRequest";
        }
        $identity_request_email = $this->getIdentityConfig(
            'identity_request_email'
        );
        if ($identity_request_email=="1") {
            $requestCategories[] = "EmailRequest";
        }

        if ($scenario == 3
            && !in_array("BirthDateRequest", $requestCategories)
        ) {
            $requestCategories[] = "BirthDateRequest";
        }

        return $requestCategories;
    }


    // retrieve something to identify the guest based on the session data
    // ip?
    private function _getGuestId()
    {
        $remote = ObjectManager::getInstance()->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
        $ip = $remote->getRemoteAddress();
        return $ip;
    }
    private function _getUserId()
    {
        $userId = $this->_customerSession->getCustomer()->getId();
        return $userId;
    }
    public function getBluemGuestIdentified()
    {
        $requestModel = ObjectManager::getInstance()->create(
            'Bluem\Integration\Model\Request'
        );
        $userId = $this->_getGuestId();
        $collection = $requestModel->getCollection();
        $collection->setOrder('created_at', 'DESC');

        $rq = null;
        $identified = false;
        foreach ($collection as $c) {
            if ($identified) {
                continue;
            }

            $d = $c->getData();
            $pl = json_decode($d['payload']);
            if (isset($pl->ip)) {
                $ip = $pl->ip;
            } else {
                continue;
            }

            if ($ip===$userId) {
                if ($d['status']==="response_success") {
                    $identified = true;
                    $rq = $d;
                }
            }
        }

        $identity = new stdClass;
        $identity->status = $identified;
        $identity->report = null;
        if ($identified === false) {
            $identity->result = "No valid request found.";
            return $identity;
        } else {
            $identity->result = "Verified" ;
            $pl = json_decode($rq['payload']);
            if (isset($pl->report)) {
                $identity->report = $pl->report;
            } else {
                $identity->report = [];
            }
            $identity->request = $rq;
        }

        return $identity;
    }

    public function getBluemUserIdentified()
    {
        $userId = $this->_getUserId();
        $requestModel = ObjectManager::getInstance()->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        $rq = null;
        $identified = false;
        foreach ($collection as $c) {
            if ($identified) {
                continue;
            }
            $d = $c->getData();
            if ((int)$d['user_id']===(int)$userId) {
                if ($d['status']==="response_success") {
                    $identified = true;
                    $rq = $d;
                }
            }
        }

        $identity = new stdClass;
        $identity->status = $identified;
        $identity->report = null;
        if ($identified === false) {
            $identity->result = "No valid request found.";
            return $identity;
        } else {
            $identity->result = "Verified" ;
            $pl = json_decode($rq['payload']);
            if (isset($pl->report)) {
                $identity->report = $pl->report;
            } else {
                $identity->report = [];
            }
            $identity->request = $rq;
        }

        return $identity;
    }
}
