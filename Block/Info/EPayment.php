<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Block\Info;

class EPayment extends \Magento\Payment\Block\Info
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


    function getPaymentRequestInfo() {
        return $this->getInfoData('order_id');
        // $orderId = $this->getRequest()->getParam('order_id');
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     
    }
}
