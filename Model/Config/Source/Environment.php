<?php

namespace Bluem\Integration\Model\Config\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'test' => 'Test environment',
            'prod' => 'Production environment (live transactions)',
        ];
    }
}