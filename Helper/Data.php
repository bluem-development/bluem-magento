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
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;

use \Carbon\Carbon;

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

    /**
     * Get config value.
     *
     * @public
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config for section.
     *
     * @public
     */
    public function getConfigForSection($section, $code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_BASE_PATH . '/' . $section . '/'. $code,
            $storeId
        );
    }

    /**
     * Get general config.
     *
     * @public
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("general", $code, $storeId);
    }

    /**
     * Get identity config.
     *
     * @public
     */
    public function getIdentityConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("identity", $code, $storeId);
    }

    /**
     * Get payments config.
     *
     * @public
     */
    public function getPaymentsConfig($code, $storeId = null)
    {
        return $this->getConfigForSection("payments", $code, $storeId);
    }

    /**
     * Get identity valid.
     * Conditions based on several scenarios.
     *
     * @public
     */
    public function getIdentityValid($not_on_status_page = true)
    {
        $identity_scenario = (int) $this->getIdentityConfig('identity_scenario');
        
        $min_age = (int) $this->getIdentityConfig('identity_min_age');

        $valid = false;
        
        $explanation_html = "";
        
        $invalid_message = "";

        $requestURL = "{$this->_baseURL}bluem/identity/request?goto=shop";
        
        $report = "";

        error_log($identity_scenario);
        
        /**
         * Require any identity check?
         *
         * 0: Do not perform any automatic identification or check
         * 1: Require a minimum age check
         * 2: Require a regular identification, but do not check on minimum age
         * 3: Require a regular identification AND check on minimum age
         */
        if ($identity_scenario >= 1)
        {
            $identity_checked = $this->_customerSession->isLoggedIn() ? 
                $this->getBluemUserIdentified() : 
                $this->getBluemGuestIdentified();
            
            if ($identity_checked->status === true) {
                $valid = true;
                
                $report = $identity_checked->report;
            } else {
                $valid = false;
                
                $invalid_message = "<a href='{$requestURL}' target='_self'>You must first identify yourself</a> before you can add products to the cart.";
            }
            
            /**
             * Get birthdate and define age in years.
             */
            if (!empty($identity_checked->report->BirthDateResponse))
            {
                try {
                    $age_in_years = Carbon::parse($identity_checked->report->BirthDateResponse)
                        ->diffInYears(Carbon::now());
                } catch (Exception $e) {
                    //
                }
            }
            
            /**
             * Scenario 1.
             * Require a minimum age check
             */
            if ($identity_scenario == 1)
            {
                if (!empty($identity_checked->report->AgeCheckResponse)) {
                    if ($identity_checked->report->AgeCheckResponse == "true"
                        || $identity_checked->report->AgeCheckResponse == "1"
                    ) {
                        $valid = true;
                    } else {
                        $valid = false;
                        $invalid_message = "The reported age from your
                            identification is not sufficient.
                            <a href='{$requestURL}'
                            target='_self'>Please identify again</a> or contact us.";
                    }
                } elseif (!empty($identity_checked->report->BirthDateResponse)) {
                    $valid = true;
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
            
            /**
             * Scenario 2.
             * Require a regular identification, but do not check on minimum age
             */
            if ($identity_scenario == 2)
            {
                //
            }
            
            /**
             * Scenario 3.
             * Require a regular identification AND check on minimum age
             */
            if ($identity_scenario == 3)
            {
                /**
                 * Check the age.
                 */
                if (!empty($identity_checked->report->BirthDateResponse)) {
                    if ($age_in_years >= $min_age) {
                        $valid = true;
                    } else {
                        $valid = false;
                        
                        $invalid_message = "Your age appears to be insufficient.
                        The minimum age of <strong>{$min_age} years</strong>
                        is required. ".
                        ($not_on_status_page?"<a href='{$this->_baseURL}bluem/identity/status' target='_blank'>View the status of your identification here</a> or contact":"Contact").
                        " us if you have any questions.";
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
        else
        {
            /**
             * No verification required. But check the status.
             */
            $identity_checked = $this->_customerSession->isLoggedIn() ? 
                $this->getBluemUserIdentified() : 
                $this->getBluemGuestIdentified();

            error_log($identity_checked);
            
            if ($identity_checked->status === true) {
                $valid = true;
                
                $report = $identity_checked->report;
            }
        }

        if (!$valid) {
            $explanation_html = "<br><small><a href='{$this->_baseURL}bluem/identity/information' target='_blank' title='Learn more about iDIN - identity validation'>What is this?</a></small>";
        }
        
        $data = (object) [
            'valid' => $valid,
            'invalid_message' => $invalid_message . $explanation_html,
            'report' => $report,
            'age_data' => [
                'age_in_years' => !empty($age_in_years) ? $age_in_years : "",
                'min_age' => !empty($min_age) ? $min_age : "",
            ]
        ];

        return $data;
    }

    /**
     * Get identity request categories.
     *
     * @public
     */
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

    /**
     * Get guest identifier.
     *
     * @private
     */
    private function _getGuestId()
    {
        $remote = ObjectManager::getInstance()->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
        $ip = $remote->getRemoteAddress();
        return $ip;
    }
    
    /**
     * Get user identifier.
     *
     * @private
     */
    private function _getUserId()
    {
        $userId = $this->_customerSession->getCustomer()->getId();
        return $userId;
    }
    
    /**
     * Get guest identification status.
     *
     * TODO; Add data to session instead by IP (think about shared computers).
     *
     * @private
     */
    public function getBluemGuestIdentified()
    {
        $requestModel = ObjectManager::getInstance()->create(
            'Bluem\Integration\Model\Request'
        );
        $userId = $this->_getGuestId();
        $collection = $requestModel->getCollection();
        $collection->setOrder('created_at', 'DESC');

        $identified = false;
        $rq = null;
        
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
        return $this->_getIdentity($identified,$rq);
    }

    /**
     * Get identity details.
     *
     * @private
     */
    private function _getIdentity($identified,$rq)
    {
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

    /**
     * Get Bluem user identified.
     *
     * @public
     */
    public function getBluemUserIdentified()
    {
        $userId = $this->_getUserId();
        $requestModel = ObjectManager::getInstance()->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        $identified = false;
        $rq = null;
        
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
        return $this->_getIdentity($identified,$rq);
    }
}
