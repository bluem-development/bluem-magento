<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Observer;

use Magento\Quote\Api\Data\PaymentInterface;

class SalesQuotePaymentBeforeSavedObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (empty($observer->getEvent()->getPayment())) {
            return $this;
        }

        $payment = $observer->getEvent()->getPayment();
        $additionalData = $payment->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (isset($additionalData['assistant_id'])) {
            $payment->setAssistantId($additionalData['assistant_id']);
        }

        return $this;
    }
}
