<?php
namespace Bluem\Integration\Block;

use \Magento\Framework\View\Element\Template\Context;

use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\App\ObjectManager;

use Bluem\Integration\Helper\Data as DataHelper;
use stdClass;

// for now only defined here
// define("BLUEM_DEBUG",false);


class Display extends \Magento\Framework\View\Element\Template
{
    protected $_identityFactory;

    protected $_customerSession;

    protected $_resourceConnection;
    
    protected $_storeManager;
    
    protected $_baseURL;

	public function __construct(
        Context $context,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        DataHelper $dataHelper
    )
	{
        $this->_resourceConnection = $resourceConnection;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;

        $this->_storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();



		parent::__construct($context);
	}

    public function getBluemRequests() {
        $requestModel = ObjectManager::getInstance()->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        return $collection;
    }
    
    public function debugMode() {
        return false;//BLUEM_DEBUG;
    }

    public function getBaseUrl() {
        return $this->_baseURL;
    }

    public function showBluemIdentityButton() 
    {
        return "<a href='{$this->_baseURL}bluem/identity/request' class='action primary' >Start identification process..</a>";
    }

    public function getUserLoggedIn()
    {
        return ($this->_customerSession->isLoggedIn());
    }

    public function getBluemIdentified() {
        return $this->_dataHelper->getBluemIdentified();
    }

    public function getUserData()
    {
        return [
            'email'=>$this->_customerSession->getCustomer()->getEmail(),
            'name'=>$this->_customerSession->getCustomer()->getName(),
            'id' => $this->_customerSession->getCustomer()->getId()
        ];
    }



    public function showProductWarning()
    {
        
        $validated_identity = $this->_dataHelper->getIdentityValid();

        // $html.=' valid? '.print_r($valid).' message: '.$invalid_message;
        // return $html;
        
        if(!$validated_identity->valid) {
            
            $html='';
            $html .='<div role="alert" class="messages">
            <div class="message-warning warning message">
            <div>';
            //. __('Je moet de identificatie procedure eerst nog doorlopen') ;
            $html.=__($validated_identity->invalid_message);
            $html.='
            </div>
            </div>
            </div>';
            return $html;
        }
        return '';
    }




function getRequestsTableHTML() 
{
    $html = "";
    $requests = $this->getBluemRequests();


    if(count($requests)>0) {

        $headers = [];
        foreach($requests as $request) {
            if(count($headers)>0) {
                continue;
            }
            foreach ($request->getData() as $k => $v) {
                $headers[] = $k;
            }
        }
        // var_dump($block->getRequests());
        $html.= "<div style='width:100%; height:auto; 
        padding:10pt; overflow-y: auto; 
        overflow-x:none; margin-top:10pt; 
        margin-bottom:10pt;'>
        <table class='data-grid'>";
        $html.= "<thead><tr>";
        foreach($headers as $h) {
            if($k == "type" ) {
                continue;
            }
            $hs = ucfirst(str_replace("_"," ",$h));
            $html.="<th style='text-align:center'>$hs</th>";
        }
        $html.= "</thead><tbody>";

        foreach($requests as $request) {
            $html.= "<tr>";
            // $html.= "<td>";
            // var_dump($request->getData());
            foreach($request->getData() as $k=>$v) {
                //{$k}<br>
                if($k == "type" ) {
                    continue;
                }
                $html.= "<td>";
                if($k == "payload")
                {
                    $v_obj = json_decode($v);
                    $html .= "<pre style='font-size:8pt; max-height:200px; overflow-y:auto; width:400px;'>";
                    $html.=print_r($v_obj,true);
                    $html.=" </pre>" ;
                } elseif($k == "transaction_url") {
                    $html .= "<a href='$v' target='_self' class='action secondary'>Open URL</a>";
                } else {
                    $html.= $v;
                }
                $html.= "</td>";
            }
            // $html.= "</td>";
            $html.= "</tr>";
        }
        $html.=" </tbody>";
        $html.= "</table></div>";
        return $html;
    } 
    return "<p>No requests yet.</p>";

}
}


/*
        // if($connection->isTableExists('bluem_integration_request')) {

        //     $table = $connection->getTableName('bluem_integration_request');
        //     //For Select query
        //     $query = "Select * FROM " . $table;
        //     $result = $connection->fetchAll($query);
            
        //     $identity->result = "Success: Verification received";
        //     $identity->payload = $result;
        // } else {
        //     $identity->result = "Error: No DB table created, reinstall and activate the module Bluem_Integration first" ;
        // }
            
 */