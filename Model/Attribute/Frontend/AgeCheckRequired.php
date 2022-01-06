<?php
namespace Bluem\Integration\Model\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\DataObject;

class AgeCheckRequired extends AbstractFrontend
{
    public function getValue(DataObject $object): string
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if ($value ==1) {
            return "<b>Identy verification possibly required</b>";
        } else {
            return "<b>Identy verification not required</b>";
        }
    }
}
