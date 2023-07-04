<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Block;

use Magento\Framework\View\Element\Template\Context;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\View\Element\Template;

use Bluem\Integration\Helper\Data as DataHelper;

class Display extends Template
{
    /**
     * The identity factory
     *
     * @var mixed
     */
    protected $_identityFactory;

    /**
     * The customer session
     *
     * @var mixed
     */
    protected $_customerSession;

    /**
     * The resource connection
     *
     * @var mixed
     */
    protected $_resourceConnection;

    /**
     * The store manager
     *
     * @var mixed
     */
    protected $_storeManager;

    /**
     * The Base URL
     *
     * @var string
     */
    protected $_baseURL;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param ResourceConnection $resourceConnection
     * @param DataHelper $dataHelper
     * @param BackendHelper $backendHelper
     *
     * @return void
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        DataHelper $dataHelper,
        BackendHelper $backendHelper
    ) {
        parent::__construct($context);
        
        $this->_resourceConnection = $resourceConnection;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_backendHelper = $backendHelper;

        $this->_storeManager = ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_baseURL =  $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get the plugin version from the composer.json file.
     *
     * @return string
     */
    protected function getPluginVersion()
    {
        $composerFile = $this->moduleDirReader->getModuleDir('', '<Vendor>_<Plugin>') . '/composer.json';

        if (!file_exists($composerFile)) {
            return 'Unknown';
        }

        $composerData = json_decode(file_get_contents($composerFile), true);
        if (isset($composerData['version'])) {
            return $composerData['version'];
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get Bluem requests
     *
     * @return colllection
     */
    public function getBluemRequests()
    {
        $requestModel = ObjectManager::getInstance()->create(Bluem\Integration\Model\Request::class);
        $collection = $requestModel->getCollection();
        return $collection;
    }

    /**
     * Debugmode
     *
     * @return bool
     */
    public function debugMode(): bool
    {
        return false;//BLUEM_DEBUG;
    }

    /**
     * Get Base URL
     *
     * @return string
     */
    public function getBaseUrl() : string
    {
        return $this->_baseURL;
    }

    /**
     * Show Bluem identity button
     *
     * @return string
     */
    public function showBluemIdentityButton() : string
    {
        return "<a href='{$this->_baseURL}bluem/identity/request' class='action primary' >Start identification procedure..</a>";
    }
    
    /**
     * Show Bluem account verification button
     *
     * @return string
     */
    public function showBluemAccountVerificationButton() : string
    {
        return "<a href='{$this->_baseURL}bluem/identity/request?verify=account&returnurl={$this->_baseURL}bluem/identity/index' class='action primary'>" . __('Start identification procedure') . "</a>";
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function getUserLoggedIn() : bool
    {
        return ($this->_customerSession->isLoggedIn());
    }
    
    /**
     * Authenticate user
     *
     * @return bool
     */
    public function authenticateUser() : bool
    {
        return ($this->_customerSession->authenticate());
    }

    /**
     * Get identity valid
     *
     * @param bool $not_on_status_page
     * @return void
     */
    public function getIdentityValid($not_on_status_page = true)
    {
        return $this->_dataHelper->getIdentityValid($not_on_status_page);
    }

    /**
     * Get user data
     *
     * @return array
     */
    public function getUserData(): array
    {
        return [
            'email'=>$this->_customerSession->getCustomer()->getEmail(),
            'name'=>$this->_customerSession->getCustomer()->getName(),
            'id' => $this->_customerSession->getCustomer()->getId()
        ];
    }

    /**
     * Display product warning.
     *
     * @return string
     * @public
     */
    public function showProductWarning(): string
    {
        $identity_block_mode = $this->_dataHelper
            ->getIdentityConfig('identity_block_mode');
        
        $identity_product_warning = $this->_dataHelper
            ->getIdentityConfig('identity_product_warning');
        
        $identity_scenario = $this->_dataHelper
            ->getIdentityConfig('identity_scenario');
        
        $check_necessary = false;
        
        if (empty($identity_block_mode)
            || (!empty($identity_block_mode)
            && $identity_block_mode == "all_products")
        ) {
            $check_necessary = true;
        }
        
        if (!empty($identity_block_mode)
            && $identity_block_mode == "product_attribute"
        ) {
            $check_necessary = true;
        }
        
        if ($identity_product_warning == "0") {
            return '';
        }
        
        if ($identity_scenario == "0") {
            return '';
        }
        
        if ($check_necessary == false) {
            return '';
        }

        if ($check_necessary) {
            $validated_identity = $this->_dataHelper->getIdentityValid();

            if (!$validated_identity->valid) {
                $html = '<div role="alert" class="messages"><div class="message-warning warning message"><div>' . __('Product warning') . '</div></div></div>';
                return $html;
            }
            return '';
        }
    }

    /**
     * Get requests table
     *
     * @param bool $type_filter
     * @return string
     */
    public function getRequestsTableHTML($type_filter = false): string
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

            $html.= "
            <div style='width:100%; height:auto; overflow-y: auto; margin-top:40px; margin-bottom:10pt;'>
            <table class='data-grid'>";
            $html.= "<thead><tr>";
            foreach ($headers as $h) {
                if ($h == "type") {
                    continue;
                }
                $hs = ucfirst(
                    str_replace(
                        ["_","id"],
                        [" ","ID"],
                        $h
                    )
                );
                $html.="<th style='text-align:center; white-space: nowrap; padding: 10px;'>$hs</th>";
            }
            $html.= "</thead><tbody>";

            foreach ($requests as $request) {
                // only show requests of this type if present
                if ($type_filter!==false && $request->getType() !== $type_filter) {
                    continue;
                }
                $html.= "<tr>";
                // var_dump($request->getData());
                foreach ($request->getData() as $k => $v) {
                    //{$k}<br>
                    if ($k == "type") {
                        continue;
                    }
                    $html.= "<td>";
                    if ($k == "order_id") {
                        $url = $this->getUrl('sales/order/view', ['order_id' => $v]);
                        $html .= "<a href='{$url}' target='_self'>{$v}</a>";
                    } elseif ($k == "payload") {
                        if (!empty($v)) {
                            $v_obj = json_decode($v);
                            if ($v_obj !== null) {
                                $pl_obj = (object) [];
                                foreach ($v_obj as $kk => $vv) {
                                    $pl_obj->$kk = $vv;
                                }
                                $html .= "<pre style='font-size:8pt; max-height:200px; overflow-y:auto; width:400px;'>" . print_r($pl_obj, true) . "</pre>";
                            }
                        }
                    } elseif ($k == "transaction_url") {
                        $html .= "<a href='$v' target='_blank' 
                        class='action secondary'>Open URL</a>";
                    } else {
                        $html.= $v;
                    }
                    $html.= "</td>";
                }
                $html.= "</tr>";
            }
            $html.=" </tbody>";
            $html.= "</table>
            </div>";
            return $html;
        }
        return "<p>No requests yet.</p>";
    }

    /**
     * Get additional idin information
     *
     * @return string
     */
    public function getAdditionalIdinInfo() : string
    {
        $idin_additional_description = $this->_dataHelper
            ->getIdentityConfig('idin_additional_description');
        return nl2br($idin_additional_description);
    }
}
