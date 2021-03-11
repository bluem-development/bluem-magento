<?php

namespace Bluem\Integration\Controller;

// require_once __DIR__ . '/../vendor/autoload.php';

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\Page;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\App\ObjectManager;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\Controller\ResultFactory;

use Bluem\Integration\Helper\Data as DataHelper;
use stdClass;
use Bluem\BluemPHP\Integration;
use Exception;

class BluemAction extends Action 
{
    
    
    /**
    * @var PageFactory
    */
    protected $_pageFactory;
    protected $_storeManager;
    protected $_identityFactory;
    protected $_dataHelper;
    
    
    protected $_bluem_environment = "test";
    /**
    * @var Integration 
    */
    protected $_bluem;
    
    /**
    * @param Context $context
    * @param PageFactory $_pageFactory
    *
    * @codeCoverageIgnore
    * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        DataHelper $dataHelper,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        ResultFactory $resultFactory
        )
    {
        $this->_pageFactory = $pageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->_resourceConnection = $resourceConnection;
        $this->_resultFactory = $resultFactory;
        
        $this->_storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();
        
        
        $bluem_config = new stdClass;
        $bluem_config->senderID = $this->_dataHelper->getGeneralConfig('sender_id'); /// "S1329";                        // The sender ID, issued by BlueM. Starts with an S, followed by a number.
        $bluem_config->environment = $this->_dataHelper->getGeneralConfig('environment'); //"test" ;                // Fill in "prod", "test" or "acc" for production, test or acceptance environment.
        $bluem_config->test_accessToken = $this->_dataHelper->getGeneralConfig('test_token'); // "86d90a62b42f2f000701a30000000173002f2f01000ad986";                // The access token to communicate with BlueM, for the test environment.
        $bluem_config->production_accessToken = $this->_dataHelper->getGeneralConfig('prod_token'); //"" ;         // The access token to communicate with BlueM, for the production environment.
        
        $bluem_config->brandID = $this->_dataHelper->getIdentityConfig('identity_brand_id'); //"DRIdentity";
        // for now, use a single brandID
        // @todo: make a separate setting for this
        $bluem_config->IDINbrandID = $bluem_config->brandID; 
        
        $bluem_config->merchantID = "" ;                     // the PRODUCTION merchant ID, to be  found on the contract you
        // have with the bank for receiving direct debit mandates.
        // NOTE that MerchantID for test environment is set automatically to a valid test value
        $bluem_config->brandID = "DRIdentity";                         // What's your BrandID? Set at BlueM
        $bluem_config->expectedReturnStatus = "failure" ;    // What status would you like to get back for a TEST transaction or status request? 
        // Possible values: none, success, cancelled, expired, failure, open, pending
        $bluem_config->merchantReturnURLBase = $this->_baseURL;  // URL to return to after finishing the process
        
        $bluem_config->IDINbrandID = "DRIdentity";
        
        $bluem_config->expected_return = "success"; 
        $bluem_config->expectedReturnStatus = "success"; 
        // legacy: will be changed to be just expectedReturnStatus from 1.1.2 version of `bluem-php`
        
        $this->_bluem = new Integration($bluem_config);
        
        
        $this->_bluem_environment = $bluem_config->environment;
        
        
        return parent::__construct(
            $context
        );
    }
        
        
    
    public function execute()
    {
        // This function is overridden in all children
        throw new Exception("Not implemented!");
    }
    
    
    protected function _setRequestData($obj,$data) {
        foreach($data as $k=>$v) {
            $fn = "set{$k}";
            $obj->$fn($v);
        }
        $obj->save();
        return $obj;
    }
    
    protected function _updateRequest($request_id,$data) {
        
        $obj = $this->_getRequestByRequestId($request_id);
        $updated_obj = $this->_setRequestData($obj,$data);
        return $updated_obj;
    }
    
    
    protected function _createRequest($request_obj) 
    {
        $request = $this->_objectManager->create('Bluem\Integration\Model\Request');
        
        // @todo: make this a loop

        // validating input data
        $data = [];
        if (isset($request_obj['Type'])
        && $request_obj['Type']!==""
        ) {
            $data['Type'] = $request_obj['Type'];
        }
        if (isset($request_obj['Description'])
        && $request_obj['Description'] !== ""
        ) {
            $data['Description'] = $request_obj['Description'];
        }
        if (isset($request_obj['TransactionId'])
        && $request_obj['TransactionId'] !== ""
        ) {
            $data['TransactionId'] = $request_obj['TransactionId'];
        }
        if (isset($request_obj['OrderId'])
        && $request_obj['OrderId'] !== ""
        ) {
            $data['OrderId'] = $request_obj['OrderId'];
        }
        if (isset($request_obj['DebtorReference'])
        && $request_obj['DebtorReference'] !== ""
        ) {
            $data['DebtorReference'] = $request_obj['DebtorReference'];
        }
        if (isset($request_obj['ReturnUrl'])
        && $request_obj['ReturnUrl'] !== ""
        ) {
            $data['ReturnUrl'] = $request_obj['ReturnUrl'];
        }
        
        if (isset($request_obj['EntranceCode']) 
        && $request_obj['EntranceCode'] !== ""
        ) {
            $data['EntranceCode'] = $request_obj['EntranceCode'];
        }
        if (isset($request_obj['TransactionID']) 
        && $request_obj['TransactionID'] !== ""
        ) {
            $data['TransactionId'] = $request_obj['TransactionID'];
        }
        if (isset($request_obj['TransactionUrl']) 
        && $request_obj['TransactionUrl'] !== ""
        ) {
            $data['TransactionUrl'] = $request_obj['TransactionUrl'];
        }
        
        if (isset($request_obj['Payload']) 
        && $request_obj['Payload'] !== ""
        ) {
            $data['Payload'] = $request_obj['Payload'];
        }
        
        if (isset($request_obj['Status']) 
        && $request_obj['Status'] !== ""
        ) {
            $data['Status'] = $request_obj['Status'];
        }

        $user_id = $this->_customerSession->getCustomer()->getId();
        if(!is_null($user_id)) {
            $data['UserId'] = $user_id;
        } else {
            $data['UserId'] = 0; // @todo: public users (to implement later)
        }

        $data['Environment'] = $this->_bluem_environment;
        $request = $this->_setRequestData($request,$data);
        return $request->getRequestId();
    }
    
    protected function _getRequests() {
        $requestModel = $this->_objectManager->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        return $collection;
    }
    
    protected function _getRequestByRequestId($request_id) {
        return $this->_getRequestByField($request_id, "request_id");
    }
    
    protected function _getRequestByTransactionId($request_id) {
        return $this->_getRequestByField($request_id, "transaction_id");
    }

    protected function _getRequestByOrderId($request_id) {
        return $this->_getRequestByField($request_id, "order_id");
    }

    // @todo: improve filtering by searching for selection of items here directly, for admin display

    private function _getRequestByField($request_id,$field) {
        
        $requestModel = $this->_objectManager->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection()->addFieldToFilter(
            $field,
            array('eq'=> $request_id)
        );
        if($collection->count()==0) {
            return false;
        }

        $obj = $collection->getFirstItem();
        return $obj;
    }
    

    
    // foreach($collection as $contact) {
    //     var_dump($contact->getData());
        // }           
}