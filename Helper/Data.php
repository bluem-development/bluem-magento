<?php

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
		
		$this->_storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
		$this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();
	}
	const XML_BASE_PATH = 'integration';

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	function getConfigForSection($section,$code,$storeId = null) {

		return $this->getConfigValue(
			self::XML_BASE_PATH . '/' . $section . '/'. $code, 
			$storeId
		);
	}

	public function getGeneralConfig($code, $storeId = null)
	{
		return $this->getConfigForSection("general",$code, $storeId);
	}
	public function getIdentityConfig($code, $storeId = null)
	{
		return $this->getConfigForSection("identity",$code, $storeId);
	}
	public function getPaymentsConfig($code, $storeId = null)
	{
		return $this->getConfigForSection("payments",$code, $storeId);
	}

	public function getIdentityValid() 
	{
		$debug = false;


		$identity_scenario = (int) $this->getIdentityConfig('identity_scenario');
        $min_age = (int) $this->getIdentityConfig('identity_min_age');

		if($debug) {

			$html="";
			$html.="<br/>identity_scenario: " .print_r($identity_scenario,true);
			$html.="<br/>block_if_not_min_age: " .print_r($this->getIdentityConfig('block_if_not_min_age'),true);
			$html.="<br/>identity_min_age: " .print_r($this->getIdentityConfig('identity_min_age'),true);
			$html.="<br/>identity_request_name: " .print_r($this->getIdentityConfig('identity_request_name'),true);
			$html.="<br/>identity_request_address: " .print_r($this->getIdentityConfig('identity_request_address'),true);
			$html.="<br/>identity_request_birthdate: " .print_r($this->getIdentityConfig('identity_request_birthdate'),true);
			$html.="<br/>identity_request_agecheck: " .print_r($this->getIdentityConfig('identity_request_agecheck'),true);
			$html.="<br/>identity_request_gender: " .print_r($this->getIdentityConfig('identity_request_gender'),true);
			$html.="<br/>identity_request_telephone: " .print_r($this->getIdentityConfig('identity_request_telephone'),true);
			$html.="<br/>identity_request_email: " .print_r($this->getIdentityConfig('identity_request_email'),true);	
			$html.="<br/>identity_requests_parsed: " .print_r($this->getIdentityRequestCategories(),true);
		}
        
        $valid =true;
        $invalid_message = "";
        if (!$this->_customerSession->isLoggedIn()) {
			return (object)[
				'valid' => true,
				'invalid_message' => ''
			];
		}
		
            
		// require an identity check at all
        if($identity_scenario >= 1) {

            $identity_checked = $this->getBluemIdentified();
            if($identity_checked->status == false) {
                $valid = false;
                $invalid_message = "<a
				href='{$this->_baseURL}bluem/identity/request?goto=shop'
				target='_self'>You must first identify yourself</a>
				before you can add products to the cart.";
            }
            if($identity_scenario == 1) {
                // also require an age check
                
				if ($debug) {
					$html.=" CUR REPORT: ".print_r($identity_checked->report);
				}

                if (isset($identity_checked->report->AgeCheckResponse) 
                    && ($identity_checked->report->AgeCheckResponse."")=="1"
                ) {
                    $valid = true;
                } else {
                    $valid = false;
                    $invalid_message = "The reported age from your
					identification is not sufficient.
					<a href='{$this->_baseURL}bluem/identity/request?goto=shop'
					target='_self'>Please identify again</a> or contact us";
                }
            }
            // definitely require any identification already
            
            // print_r($identity_checked->report);
            // var_dump($identity_checked->report->BirthDateResponse."");
            $now_time = strtotime("now");
            $then_time = strtotime($identity_checked->report->BirthDateResponse."");
            
            $diff_sec = $now_time - $then_time;
            
            $age_in_years = $diff_sec / 60 / 60/ 24 / 365;
            // echo "<br>now_time = {$now_time}";
// echo "<br>then_time = {$then_time}";
// echo "<br>diff_sec = {$diff_sec}";
// echo "<br>age_in_years = {$age_in_years}";
            // die();
            if($identity_scenario == 3) {
                // also require age check
                if (isset($identity_checked->report->BirthDateResponse) 
                    && ($identity_checked->report->BirthDateResponse."")!==""
                ) {
                    
                    // calculate difference
                    $now_time = strtotime("now");
                    
                    $then_time = strtotime($identity_checked->report->BirthDateResponse."");
                    
                    $diff_sec = $now_time - $then_time;
                    
                    $age_in_years = round($diff_sec / 60 / 60/ 24 / 365, 0);
                    

                    if($age_in_years < $min_age) {
                        $valid = false;
                        $invalid_message = "Your age appears to be insufficient.
						The minimum age of {$min_age} years is required.
						Contact us if you have any questions";

                    } else {

                        $valid = true;
                    }


                } else {
                    $valid = false;
                    $invalid_message = "We could not verify your age.
					<a href='{$this->_baseURL}bluem/identity/request?goto=shop' 
					target='_self'>Please identify again</a> 
					or contact us if you have any questions";
                }        
            }
        }
        return (object)[
			'valid'=>$valid,
			'invalid_message'=>$invalid_message
		];
	}


    public function getIdentityRequestCategories() 
    {

        $scenario = (int) $this->getIdentityConfig('identity_scenario');
        
        if($scenario == 1) {
            return ["AgeCheckRequest"];
        }

        $requestCategories = [
            "CustomerIDRequest"
        ];
        
        $identity_request_name = $this->getIdentityConfig('identity_request_name');
        if($identity_request_name=="1") {
            $requestCategories[] = "NameRequest";
        }
        $identity_request_address = $this->getIdentityConfig('identity_request_address');
        if($identity_request_address=="1") {

            $requestCategories[] = "AddressRequest";
        }
        $identity_request_birthdate = $this->getIdentityConfig('identity_request_birthdate');
        if($identity_request_birthdate=="1") {
            $requestCategories[] = "BirthDateRequest";
        }
        $identity_request_gender = $this->getIdentityConfig('identity_request_gender');
        if($identity_request_gender=="1") {

            $requestCategories[] = "GenderRequest";
        }
        $identity_request_telephone = $this->getIdentityConfig('identity_request_telephone');
        if($identity_request_telephone=="1") {

            $requestCategories[] = "TelephoneRequest";
        }
        $identity_request_email = $this->getIdentityConfig('identity_request_email');
        if($identity_request_email=="1") {
            
            $requestCategories[] = "EmailRequest";
        }

        if ($scenario == 3 
			&& !in_array("BirthDateRequest", $requestCategories)
		) {
            $requestCategories[] = "BirthDateRequest";
        }
                
        return $requestCategories;
    }

	public function getBluemIdentified() 
    {

        $userId = $this->_customerSession->getCustomer()->getId();
        
        $requestModel = ObjectManager::getInstance()->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        $rq = null;
        $identified = false;
        foreach($collection as $c) {
            if($identified) {
                continue;
            }
            $d = $c->getData();
            if((int)$d['user_id']===(int)$userId) {
                if($d['status']==="response_success") {
                    $identified = true;
                    $rq = $d;
                }
            }
        }

        // var_dump($rqs);
        // exit;
        $identity =new stdClass;
        $identity->status = $identified;
        $identity->report = null;
        if($identified === false) {
            $identity->result = "No valid request found.";
            return $identity;
        } else {
            $identity->result = "Verified" ;
            $pl = json_decode($rq['payload']);
            if (isset($pl->report))  {
                $identity->report = $pl->report;
            }else {
                 $identity->report = [];
             }
            $identity->request = $rq;        
        }

        return $identity;
    }
}