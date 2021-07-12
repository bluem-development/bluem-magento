<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 */

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

// require __DIR__ . '/../BluemAction.php';

class Information extends BluemAction
{
    /**
     * Prints the Identity from informed order id
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        return $this->_pageFactory->create();
    }
}
