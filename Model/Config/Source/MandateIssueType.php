<?php

namespace Bluem\Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class MandateIssueType implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'CORE',
                'label' => __('CORE machtiging'),
            ],
            [
                'value' => 'B2B',
                'label' => __('B2B machtiging (zakelijk)'),
            ],
        ];
    }
}
