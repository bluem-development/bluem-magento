<?php
namespace Bluem\Integration\Model\Attribute\Frontend;

class AgeCheckRequired extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    public function getValue(\Magento\Framework\DataObject $object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if($value ==1) {

            return "<b>Identy verification possibly required</b>";
        } else {
            return "<b>Identy verification not required</b>";
        }
    }
}