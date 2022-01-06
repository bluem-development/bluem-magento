<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
/* Based on  php-cuong/magento-offline-payments  */
namespace Bluem\Integration\Block\Info;

use Magento\Payment\Block\Info;

class EPayment extends Info
{
    /**
     * @var string
     */
    protected $_template = 'Bluem_Integration::info/epayment.phtml';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Bluem_Integration::info/pdf/epayment.phtml');
        return $this->toHtml();
    }


    public function getPaymentRequestInfo()
    {
        return $this->getInfoData('order_id');
        // $orderId = $this->getRequest()->getParam('order_id');
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
}
