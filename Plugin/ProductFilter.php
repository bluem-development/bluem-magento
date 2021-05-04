<?php
namespace Bluem\Integration\Plugin;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ObjectManager;
use stdClass;
use Bluem\Integration\Helper\Data as DataHelper;
use Bluem\Integration\Helper\Bluem as BluemHelper;
use \Magento\Catalog\Model\Product ;
use Throwable;

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
        $identity_product_agecheck_attribute = $this->_dataHelper->getIdentityConfig('identity_product_agecheck_attribute');

        $product = $this->productRepository->getById($product);
        try {
            $attr = $product->getData($identity_product_agecheck_attribute);

        } catch(Throwable $th) {
            // error in retrieving the data? then just allow the checkout for now
            return true;
        }

        if ($attr == "1"
            || $attr == "true"
            || $attr == true
        ) {
            $identity_valid = $this->_dataHelper->getIdentityValid();
            return $identity_valid->valid;
        } else {
            return true;
        }

    }
}