<?php

namespace Bluem\Integration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class MandateSequenceType implements ArrayInterface
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
                'value' => 'RCUR',
                'label' => __('Doorlopende machtiging (recurring)'),
            ],
            [
                'value' => 'OOFF',
                'label' => __('Eenmalige machtiging (one-time)'),
            ],
        ];
    }
}
