<?php

namespace Bluem\Integration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 *  Request Resource Model
 *
 *  Reference: https://www.pierrefay.com/magento2-training/create-magento2-model-database.html
 *
 * @author Daan Rijpkema
 */
class Request extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('bluem_integration_request', 'request_id');
    }
}
