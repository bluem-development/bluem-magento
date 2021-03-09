<?php

namespace Bluem\Integration\Model\ResourceModel\Request;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Request Resource Model Collection
 *
 * @author      Daan Rijpkema
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Bluem\Integration\Model\Request', 'Bluem\Integration\Model\ResourceModel\Request');
    }
}
