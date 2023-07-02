<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Bluem\Integration\Helper\Data as DataHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use stdClass;
use Throwable;

class ProductFilter
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        Session $customerSession,
        DataHelper $dataHelper,
        ProductRepository $productRepository
    ) {
        $this->_customerSession = $customerSession;

        $this->productRepository = $productRepository;
        $this->_dataHelper = $dataHelper;
    }

    public function afterIsSaleable(Product $product)
    {
        $filter_debug = false;
        
        $identity_product_warning = $this->_dataHelper
            ->getIdentityConfig('identity_product_warning');
        
        $identity_scenario = $this->_dataHelper
            ->getIdentityConfig('identity_scenario');
        
        if ($identity_product_warning === '0') {
            return true;
        } elseif ($identity_scenario === '0') {
            return true;
        }

        /**
         * Check if domain whitelisting is setup
         * (config setting is not set to * or empty)
         * and if the current server name matches any
         * of the given domains matches
         */
        $domain_whitelist = $this->_dataHelper
            ->getIdentityConfig('identity_domain_whitelist');

        if (!empty($domain_whitelist) && $domain_whitelist !== "*") {
            $current_domain = $_SERVER['SERVER_NAME'];

            $domain_is_whitelisted = false;
            $domains = explode(',', $domain_whitelist);

            if (count($domains) > 0) {
                foreach ($domains as $domain) {
                    if (empty($domain)) {
                        continue;
                    }
                    $domain_parts = explode("?", $domain, 2);
                    $domain_sanitized = strtolower(
                        trim(
                            str_replace(
                                ["https://","http://"],
                                '',
                                $domain_parts[0]
                            )
                        )
                    );
                    // Always allow this product if domain is not whitelisted 
                    // - The filter is not used when not on the whitelisted domain
                    if ($current_domain === $domain_sanitized) {
                        if ($filter_debug) {
                            var_dump("Whitelisted");
                        }
                        continue;
                    } else {
                        return true;
                    }
                }
            }
        }

        $identity_block_mode = $this->_dataHelper
            ->getIdentityConfig('identity_block_mode');
        
        if ($filter_debug) {
            var_dump("Initiating product filter");
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
            $identity_product_agecheck_attribute = $this->_dataHelper
                ->getIdentityConfig('identity_product_agecheck_attribute');
            
            // Fallback to default attribute
            if (is_null($identity_product_agecheck_attribute)) {
                $identity_product_agecheck_attribute = "agecheck_required";
            }
            try {
                $attr = $product->getData($identity_product_agecheck_attribute);
                if ($filter_debug) {
                    var_dump($attr);
                }
            } catch (Throwable $th) {
                if ($filter_debug) {
                    var_dump("ERROR in productfilter");
                }
                // Error in retrieving the data? Then just allow the checkout for now
                $check_necessary = false;
            }
            if (is_null($attr)) {
                if ($filter_debug) {
                    var_dump("Emtpy in productfilter");
                }
                // Attribute is not set? Then just allow the checkout for now
                $check_necessary = false;
            } else {
                if ($attr == "1"
                    || $attr == "true"
                    || $attr == true
                ) {
                    $check_necessary = true;
                }
            }
            if ($filter_debug) {
                var_dump("Success in productfilter");
            }
        }

        if ($filter_debug) {
            var_dump("Check necessary? " . ($check_necessary ? "Yes" : "No"));
        }
        // No check? Then just say s'all good
        if ($check_necessary == false) {
            return true;
        }

        if ($check_necessary) {
            $identity_valid = $this->_dataHelper->getIdentityValid();
            if ($filter_debug) {
                var_dump("Identity valid? " . ($identity_valid->valid ? "Yes" : "No"));
                var_dump($identity_valid);
            }
            return $identity_valid->valid;
        } else {
            return true;
        }
    }
}

// https://magento.stackexchange.com/questions/165218/disable-entire-cart-functionality-checkout-in-magento-2
// https://magento.stackex  change.com/questions/304604/how-to-hide-add-to-cart-button-for-particular-products-on-all-page-in-magento-2
// https://www.mageplaza.com/magento-2-module-development/magento-2-add-product-attribute-programmatically.html
// https://devdocs.magento.com/videos/fundamentals/add-new-product-attribute/
