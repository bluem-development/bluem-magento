<?php

namespace Bluem\Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class IdentityBlockMode implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'all_products' => 'All products (default)',
            'product_attribute' => 'Based on specified product attribute set to 1',
        ];
    }
}
