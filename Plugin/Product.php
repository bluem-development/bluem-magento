<?php
namespace Bluem\Integration\Plugin;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ObjectManager;
use stdClass;
use Bluem\Integration\Helper\Data as DataHelper;
use Bluem\Integration\Helper\Bluem as BluemHelper;


class Product
{
    protected $_customerSession;

    public function __construct(
        Session $customerSession,
        DataHelper $dataHelper,
        BluemHelper $bluemHelper
    ) {

        $this->_customerSession = $customerSession;

        $this->_dataHelper = $dataHelper;
        $this->_bluemHelper = $bluemHelper;
    }           
    
    // https://magento.stackexchange.com/questions/165218/disable-entire-cart-functionality-checkout-in-magento-2
    public function afterIsSaleable(\Magento\Catalog\Model\Product $product)
    {           
        $identity_valid = $this->_dataHelper->getIdentityValid();
        return $identity_valid->valid;
        // $block_if_not_identified = $this->_dataHelper->getGeneralConfig('block_if_not_identified');
        
        // if($this->_customerSession->isLoggedIn()) {
            // $identity = $this->getBluemIdentified();
            // return $identity->status;
        //     return true;
        // }
        
        // return true;

    }


    // copied from display
    

}