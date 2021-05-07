<?php
namespace Bluem\Integration\Plugin;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ObjectManager;
use stdClass;
use Bluem\Integration\Helper\Data as DataHelper;
use Bluem\Integration\Helper\Bluem as BluemHelper;
use \Magento\Catalog\Model\Product ;
use \Magento\Catalog\Model\ProductRepository;
use Throwable;

class ProductFilter
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    public function __construct(
        Session $customerSession,
        DataHelper $dataHelper,
        BluemHelper $bluemHelper,
        ProductRepository $productRepository
    ) {

        $this->_customerSession = $customerSession;

        $this->productRepository = $productRepository;
        $this->_dataHelper = $dataHelper;
        $this->_bluemHelper = $bluemHelper;
    }

    public function afterIsSaleable(\Magento\Catalog\Model\Product $product)
    {
        $identity_product_agecheck_attribute = $this->_dataHelper
            ->getIdentityConfig('identity_product_agecheck_attribute');
        // fallback
        if (is_null($identity_product_agecheck_attribute)) {
            $identity_product_agecheck_attribute = "agecheck_required";
        }
        try {
            $attr = $product->getData($identity_product_agecheck_attribute);
            // var_dump($attr);
            // die();
            
        } catch(Throwable $th) {
            // error in retrieving the data? then just allow the checkout for now
            return true;
        }
        if (is_null($attr)) {
            // attribute is not set? then just allow the checkout for now
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

// https://magento.stackexchange.com/questions/165218/disable-entire-cart-functionality-checkout-in-magento-2
// https://magento.stackexchange.com/questions/304604/how-to-hide-add-to-cart-button-for-particular-products-on-all-page-in-magento-2    
// https://www.mageplaza.com/magento-2-module-development/magento-2-add-product-attribute-programmatically.html
// https://devdocs.magento.com/videos/fundamentals/add-new-product-attribute/