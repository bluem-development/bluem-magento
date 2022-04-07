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
            '0' => __('Do not perform any automatic identification or check'),
            '1' => __('Require a minimum age check'),
            '2' => __('Require a regular identification, but do not check on minimum age'),
            '3' => __('Require a regular identification AND check on minimum age'),
        ];
    }
}
