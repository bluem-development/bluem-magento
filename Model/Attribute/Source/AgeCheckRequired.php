	

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bluem\Integration\Model\Attribute\Source;

class AgeCheckRequired extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
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
