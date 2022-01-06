<?php

namespace Bluem\Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            'test' => 'Test environment',
            'prod' => 'Production environment (live transactions)',
        ];
    }
}
