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

require_once __DIR__ . '/../BluemWebhookAction.php';

use Bluem\Integration\Controller\BluemWebhookAction;

class Webhook extends BluemWebhookAction
{
    // public function execute()
    // {
    //     $this->_logger->info('Webhook request received');
    //     $this->_logger->info(print_r($this->getRequest()->getParams(), true));

    //     $this->_logger->info('Webhook request processing');

    //     $this->_logger->info('Webhook request processed');
    //     $this->_logger->info('Webhook request completed');
    // }
}
