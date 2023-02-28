<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;

use stdClass;
use Exception;

use Bluem\Integration\Helper\Data as DataHelper;
use Bluem\BluemPHP\Bluem as Bluem;

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
    * @var Bluem
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
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->_resourceConnection = $resourceConnection;
        $this->_resultFactory = $resultFactory;

        $this->_storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();

        $bluem_config = new stdClass;

        // The sender ID, issued by BlueM. Starts with an S, followed by a number.
        $bluem_config->senderID = $this->_dataHelper->getGeneralConfig('sender_id');

        // Fill in "prod", "test" or "acc" for production, test or acceptance environment.
        $bluem_config->environment = $this->_dataHelper->getGeneralConfig('environment'); //"test" ;
        // The access token to communicate with BlueM, for the test environment.
        $bluem_config->test_accessToken = $this->_dataHelper->getGeneralConfig('test_token');
        $bluem_config->production_accessToken = $this->_dataHelper->getGeneralConfig('prod_token'); //"" ;         // The access token to communicate with BlueM, for the production environment.

        // What's your BrandID? Set at Bluem
        $bluem_config->brandID = $this->_dataHelper->getPaymentsConfig('payments_brand_id'); //"DRIdentity";
        // for now, use a single brandID
        $bluem_config->IDINbrandID = $this->_dataHelper->getIdentityConfig('identity_brand_id');

        $bluem_config->merchantReturnURLBase = $this->_baseURL;  // URL to return to after finishing the process

        // What status would you like to get back for a TEST transaction or status request?
        // Possible values: none, success, cancelled, expired, failure, open, pending
        $bluem_config->expected_return = "success";
        $bluem_config->expectedReturnStatus = "success";
        // legacy: will be changed to be just expectedReturnStatus from 1.1.2 version of `bluem-php`

        $this->_bluem = new Bluem($bluem_config);
        $this->_bluem_environment = $bluem_config->environment;

        return parent::__construct(
            $context
        );
    }

    public function execute()
    {
        // This function is overridden in all children, so it is not necessary here.
        throw new Exception("Not implemented!");
    }

    /**
     * Set the request object data
     *
     * @param object $obj
     * @param $data
     * @return object
     */
    protected function _setRequestData($obj, $data)
    {
        foreach ($data as $k=>$v) {
            $fn = "set{$k}";
            $obj->$fn($v);
        }
        $obj->save();
        return $obj;
    }

    protected function _updateRequest($request_id, $data)
    {
        $obj = $this->_getRequestByRequestId($request_id);
        return $this->_setRequestData($obj, $data);
    }

    protected function _createRequest($request_obj)
    {
        $request = $this->_objectManager->create('Bluem\Integration\Model\Request');

        // validating input data
        $data = [];
        if (!empty($request_obj['Type'])) {
            $data['Type'] = $request_obj['Type'];
        }
        if (!empty($request_obj['Description'])) {
            $data['Description'] = $request_obj['Description'];
        }
        if (!empty($request_obj['TransactionId'])) {
            $data['TransactionId'] = $request_obj['TransactionId'];
        }
        if (!empty($request_obj['OrderId'])) {
            $data['OrderId'] = $request_obj['OrderId'];
        }
        if (!empty($request_obj['DebtorReference'])) {
            $data['DebtorReference'] = $request_obj['DebtorReference'];
        }
        if (!empty($request_obj['ReturnUrl'])) {
            $data['ReturnUrl'] = $request_obj['ReturnUrl'];
        }

        if (!empty($request_obj['EntranceCode'])) {
            $data['EntranceCode'] = $request_obj['EntranceCode'];
        }
        if (!empty($request_obj['TransactionID'])) {
            $data['TransactionId'] = $request_obj['TransactionID'];
        }
        if (!empty($request_obj['TransactionUrl'])) {
            $data['TransactionUrl'] = $request_obj['TransactionUrl'];
        }
        
        if (!empty($request_obj['Payload'])) {
            $data['Payload'] = $request_obj['Payload'];
        }

        if (!empty($request_obj['Status'])) {
            $data['Status'] = $request_obj['Status'];
        }

        $user_id = $this->_customerSession->getCustomer()->getId();
        if (!is_null($user_id)) {
            $data['UserId'] = $user_id;
        } else {
            $data['UserId'] = "";
            
            $remote = ObjectManager::getInstance()->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
            $ip = $remote->getRemoteAddress();
            $data['UserId'] = str_replace('.', '', $ip);
        }

        $data['Environment'] = $this->_bluem_environment;
        var_dump($data); exit;
        $request = $this->_setRequestData($request, $data);
        return $request->getRequestId();
    }

    protected function _getRequests()
    {
        $requestModel = $this->_objectManager->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        return $collection;
    }

    protected function _getRequestByRequestId($request_id)
    {
        return $this->_getRequestByField($request_id, "request_id");
    }

    protected function _getRequestByTransactionId($request_id)
    {
        return $this->_getRequestByField($request_id, "transaction_id");
    }

    protected function _getRequestByOrderId($request_id)
    {
        return $this->_getRequestByField($request_id, "order_id");
    }

    /**
     * searching for selection of items here directly, for admin display
     *
     * @param [type] $request_id
     * @param [type] $field
     * @return object|boolean
     */
    private function _getRequestByField($request_id, $field)
    {
        $requestModel = $this->_objectManager->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection()->addFieldToFilter(
            $field,
            array('eq'=> $request_id)
        );
        if ($collection->count() == 0) {
            return false;
        }

        return $collection->getFirstItem();
    }

    // Helper functions for layout   
    private function _wrapSimplePage($mixed_content) {
        $home_url = $this->_baseURL."";
        
        return "<html lang='en'><body style='font-family:Arial, sans-serif;'>
            <div style='max-width:500px; margin:0 auto;
            padding:15pt; display:block;'>
            {$mixed_content}
            <br>
            <p><a href='".$home_url."' class='bluem-button' target='_self'>Go back to {$home_url}</a></p>
            </div></body></html>";
        // @todo localize
    }

    /**
     * Get generic Error message HTML promipt
     *
     * @param [type] $error_message
     * @return void
     */
    protected function _getErrorMessageHtml(
        $error_details,
        $error_header="Invalid response received", 
        $include_cta=true
    ) : String {
        $error_message_html = "<h2>$error_header</h2>";
        if ($include_cta) {
            $error_message_html .= "<p>Please contact your webshop administrator. More information about the response: </p>";
        }
        $error_message_html .= "<p>$error_details</p>";
        // @todo add a go back link
        return $this->_wrapSimplePage($error_message_html);
        
    }
    
    /**
     * Rendering an intermediate page
     *
     * @param string $h Header text
     * @param string $b Body text
     *
     * @return void
     */
    protected function _showMiniPrompt(string $h, string $b)
    {
        echo $this->_wrapSimplePage("<h2>{$h}</h2><div class='bluem-content'>{$b}</div>");
    }
}
