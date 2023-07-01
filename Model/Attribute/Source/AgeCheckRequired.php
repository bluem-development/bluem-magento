<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bluem\Integration\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class AgeCheckRequired extends AbstractSource
{
    /**
     * Get all options
     * 
     * @return array
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('No'), 'value' => '0'],
                ['label' => __('Yes'), 'value' => '1']
            ];
        }
        return $this->_options;
    }
}
