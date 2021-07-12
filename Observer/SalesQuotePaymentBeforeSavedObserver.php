<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Observer;

use Magento\Quote\Api\Data\PaymentInterface;
use \Magento\Framework\Event\Observer;

class SalesQuotePaymentBeforeSavedObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
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
