<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Peter Meester <p.meester@bluem.nl>
 */

namespace Bluem\Integration\Controller\Customer;

class Verification extends \Magento\Framework\App\Action\Action { 
    public function execute() {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
