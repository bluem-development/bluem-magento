<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Mandate;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Bluem\BluemPHP\Bluem as Bluem;

use stdClass;

class Getbanks extends Action
{
    /**
     * @var Bluem
     */
    protected $_bluem;

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $banks = [];

        $bluem_config = new stdClass;

        $this->_bluem = new Bluem($bluem_config);

        $bics = $this->_bluem->retrieveBICCodesForContext('Mandates');
        var_dump($bics);
    }
}
