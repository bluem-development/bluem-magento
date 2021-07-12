<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Controller;

use Exception;

require_once __DIR__ . '/BluemAction.php';

/**
 * Generic Webhook action for all services for Bluem
 */
class BluemWebhookAction extends BluemAction
{
    /**
     * The name of the service that this class is serving Webhook functions for
     *
     * @var String
     */
    private $_bluem_service;

    /**
     * Initializing this class
     *
     */
    public function __construct()
    {
        switch (get_class($this)) {
            case 'Bluem\Integration\Controller\Payment\Webhook\Interceptor':
                $this->_bluem_service = "Payments";
                break;
            case 'Bluem\Integration\Controller\Identity\Webhook\Interceptor':
                $this->_bluem_service = "Identity";
                break;
            case 'Bluem\Integration\Controller\Mandates\Webhook\Interceptor':
                $this->_bluem_service = "Mandates";
                break;
        }
    }

    /**
     * Execute the webhook function at any request to this endpoint
     *
     * @return void
     */
    public function execute()
    {
        echo "webhook for " . $this->_bluem_service."...";
    }
}
