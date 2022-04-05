<?php

namespace Bluem\Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class IdentityScenario implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            '0' => 'Do not perform any automatic identification or check',
            '1' => 'Block the shopping procedure based on minimum age and require an minimum age Check request',
            '2' => 'Require a regular identification before allowing shopping, but do not check on minimum age',
            '3' => 'Require a regular identification before allowing shopping AND check on minimum age',
            '4' => 'Require a regular identification in checkout AND check on minimum age',
            '5' => 'Require a regular identification in checkout AND DO NOT check on minimum age',
        ];
    }
}
