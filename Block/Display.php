<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Block;

use \Magento\Framework\View\Element\Template\Context;

use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\App\ObjectManager;
use \Magento\Backend\Helper\Data as BackendHelper;
use \Magento\Framework\View\Element\Template;

use Bluem\Integration\Helper\Data as DataHelper;

class Display extends Template
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
        DataHelper $dataHelper,
        BackendHelper $backendHelper
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_backendHelper = $backendHelper;

        $this->_storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();

        parent::__construct($context);
    }

    public function getBluemRequests()
    {
        $requestModel = ObjectManager::getInstance()->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection();
        return $collection;
    }

    public function debugMode()
    {
        return false;//BLUEM_DEBUG;
    }

    public function getBaseUrl()
    {
        return $this->_baseURL;
    }

    public function showBluemIdentityButton()
    {
        return "<a href='{$this->_baseURL}bluem/identity/request' class='action primary' >Start identification procedure..</a>";
    }

    public function getUserLoggedIn()
    {
        return ($this->_customerSession->isLoggedIn());
    }

    public function getIdentityValid()
    {
        return $this->_dataHelper->getIdentityValid();
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
        // copied from ProductFilter.php@afterIsSaleable
        $filter_debug = false;
        // $product = $this->getProduct();
        $identity_block_mode = $this->_dataHelper
            ->getIdentityConfig('identity_block_mode');
        
        if ($filter_debug) {
            echo "Initiating product filter";
            var_dump($identity_block_mode);
        }
        $check_necessary = false;
        if (is_null($identity_block_mode)
            || (!is_null($identity_block_mode)
            && $identity_block_mode =="all_products")
        ) {
            $check_necessary = true;
        }
        if (!is_null($identity_block_mode)
            && $identity_block_mode =="product_attribute"
        ) {
            // $identity_product_agecheck_attribute = $this->_dataHelper
            //             ->getIdentityConfig('identity_product_agecheck_attribute');
            // // fallback
            // if (is_null($identity_product_agecheck_attribute)) {
            //     $identity_product_agecheck_attribute = "agecheck_required";
            // }
            // try {
            //     $attr = $product->getData($identity_product_agecheck_attribute);
            //     // var_dump($attr);
            //             // die();
            // } catch (Throwable $th) {
            //     if ($filter_debug) {
            //         echo "ERROR in productfilter";
            //     }
            // error in retrieving the data? then just allow the checkout for now
            $check_necessary = true;
            // }
            // if (is_null($attr)) {
            //     if ($filter_debug) {
            //         echo "Emtpy in productfilter";
            //     }
            //     // attribute is not set? then just allow the checkout for now
            //     $check_necessary = false;
            // } else {
            //     if ($attr == "1"
            //         || $attr == "true"
            //         || $attr == true
            //     ) {
            //         $check_necessary = true;
            //     }
            // }
            // echo "Success in productfilter";
        }
        
        if ($filter_debug) {
            echo "Check necessary? " . ($check_necessary?"Yes":"No");
        }
        // no check? then just say s'all good
        if ($check_necessary == false) {
            return '';
        }


        if ($check_necessary) {
            // $identity_valid = $this->_dataHelper->getIdentityValid();
            // if ($filter_debug) {
            //     echo "Identity valid? " . ($identity_valid->valid?"Yes":"No");
            //     var_dump($identity_valid);
            // }
            $validated_identity = $this->_dataHelper->getIdentityValid();

            if (!$validated_identity->valid) {
                $html='';
                $html .='<div role="alert" class="messages">
            <div class="message-warning warning message">
            <div>';
                $html.=__($validated_identity->invalid_message);
                $html.='
            </div>
            </div>
            </div>';
                return $html;
            }
            return '';
        }
    }

    public function getRequestsTableHTML($type_filter = false)
    {
        $html = "";
        $requests = $this->getBluemRequests();

        if (count($requests)>0) {
            $headers = [];
            foreach ($requests as $request) {
                if (count($headers)>0) {
                    continue;
                }
                foreach ($request->getData() as $k => $v) {
                    if ($k == "type") {
                        continue;
                    }
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
            foreach ($headers as $h) {
                if ($k == "type") {
                    continue;
                }
                $hs = ucfirst(
                    str_replace(
                        ["_","id"],
                        [" ","ID"],
                        $h
                    )
                );
                $html.="<th style='text-align:center'>$hs</th>";
            }
            $html.= "</thead><tbody>";

            foreach ($requests as $request) {
                // only show requests of this type if present
                if ($type_filter!==false && $request->getType() !== $type_filter) {
                    continue;
                }
                $html.= "<tr>";
                // $html.= "<td>";
                // var_dump($request->getData());
                foreach ($request->getData() as $k=>$v) {
                    //{$k}<br>
                    if ($k == "type") {
                        continue;
                    }
                    $html.= "<td>";
                    if ($k == "order_id") {
                        $url = $this->getUrl('sales/order/view', ['order_id' => $v]);
                        $html .= "<a href='{$url}' target='_self'>{$v}</a>";
                    } elseif ($k == "payload") {
                        $v_obj = json_decode($v);
                        $pl_obj = new RequestPayload();
                        foreach ($v_obj as $k=>$v) {
                            $pl_obj->$k = $v;
                        }
                        $html .= "<pre style='font-size:8pt; max-height:200px; 
                        overflow-y:auto; width:400px;'>";
                        $html.=print_r($pl_obj, true);
                        $html.=" </pre>" ;
                    } elseif ($k == "transaction_url") {
                        $html .= "<a href='$v' target='_self' 
                        class='action secondary'>Open URL</a>";
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


    public function getAdditionalIdinInfo()
    {
        $idin_additional_description = $this->_dataHelper
            ->getIdentityConfig('idin_additional_description');
        return nl2br($idin_additional_description);
    }
}

// starting to make use of elegant classes, here:
class RequestPayload
{
}
