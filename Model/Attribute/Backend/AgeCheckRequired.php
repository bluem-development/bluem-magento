<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bluem\Integration\Model\Attribute\Backend;

class AgeCheckRequired extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Validate
     * @param \Magento\Catalog\Model\Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validate($object)
    {
        // $value = $object->getData($this->getAttribute()->getAttributeCode());
        // if ( ($object->getAttributeSetId() == 10) && ($value == 'wool')) {
        //     throw new \Magento\Framework\Exception\LocalizedException(
        //         __('Bottom can not be wool.')
        //     );
        // }
        return true;
    }

    /*
        The backend model may have beforeSave, afterSave, and afterLoad methods that allow the execution of some code at the moment an attribute is saved or loaded. The backend model is what makes attribute management a really powerful method of customization.
        Note that we hardcoded attributeSetId here for the sake of time. In other cases, it could be different. 
        Make sure to check the eav_attribute_set table for the right ID. 
    */
}
