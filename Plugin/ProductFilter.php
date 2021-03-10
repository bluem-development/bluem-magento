<?php
namespace Bluem\Integration\Plugin;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ObjectManager;
use stdClass;
use Bluem\Integration\Helper\Data as DataHelper;
use Bluem\Integration\Helper\Bluem as BluemHelper;
use \Magento\Catalog\Model\Product ;


class ProductFilter
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
    public function afterIsSaleable(Product $product)
    {           
        $identity_valid = $this->_dataHelper->getIdentityValid();
        return $identity_valid->valid;
    }
}