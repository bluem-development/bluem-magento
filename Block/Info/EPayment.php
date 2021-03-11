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

        $orderId = $this->getRequest()->getParam('order_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($orderId);
                
        $requestModel = $objectManager->create('Bluem\Integration\Model\Request');
        $collection = $requestModel->getCollection()->addFieldToFilter(
            'order_id',
            array('eq'=> $orderId)
        );
        if ($collection->count()==0) {
            return false;
        }

        $obj = $collection->getFirstItem();
        return $obj;
        // return $orderId;
    }
}
