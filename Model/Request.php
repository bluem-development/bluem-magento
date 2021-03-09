<?php

namespace Bluem\Integration\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;
/**
 * Reference: https://www.pierrefay.com/magento2-training/create-magento2-model-database.html
 * Structure:
 * +------------------+------------------+------+-----+---------------------+-------------------------------+
 * | Field            | Type             | Null | Key | Default             | Extra                         |
 * +------------------+------------------+------+-----+---------------------+-------------------------------+
 * | request_id       | int(10) unsigned | NO   | PRI | NULL                |                               |
 * | user_id          | int(11)          | YES  |     | NULL                |                               |
 * | transaction_id   | varchar(255)     | YES  |     | NULL                |                               |
 * | entrance_code    | varchar(255)     | YES  |     | NULL                |                               |
 * | transaction_url  | varchar(255)     | YES  |     | NULL                |                               |
 * | description      | varchar(255)     | YES  |     | NULL                |                               |
 * | debtor_reference | varchar(255)     | YES  |     | NULL                |                               |
 * | type             | varchar(32)      | YES  |     | identity            |                               |
 * | status           | varchar(32)      | YES  |     | open                |                               |
 * | payload          | text             | YES  |     | ''                  |                               |
 * | created_at       | timestamp        | NO   |     | current_timestamp() |                               |
 * | updated_at       | timestamp        | NO   |     | current_timestamp() | on update current_timestamp() |
 * +------------------+------------------+------+-----+---------------------+-------------------------------+
 */
/**
 * Request Model
 *
 * @author      Daan Rijpkema
 */
class Request extends AbstractModel
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bluem\Integration\Model\ResourceModel\Request::class);
    }
    
}