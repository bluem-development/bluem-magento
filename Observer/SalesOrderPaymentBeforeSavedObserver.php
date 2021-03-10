<?php
/* Based on  php-cuong/magento-offline-payments  */

namespace Bluem\Integration\Observer;

class SalesOrderPaymentBeforeSavedObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->inputParamsResolver = $inputParamsResolver;
        $this->requestInterface = $requestInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        if (empty($payment)) {
            return $this;
        }

        if ($payment->getMethod() != 'pdqpayment') {
            return $this;
        }

        if ($payment->getAssistantId()) {
            return $this;
        }

        // This is used while creating an order from the backend by an administrator.
        if ($this->requestInterface->getFullActionName() == 'sales_order_create_save') {
            $paymentFromPosting = $this->requestInterface->getParam('payment');
            if ($paymentFromPosting && isset($paymentFromPosting['assistant_id'])) {
                $payment->setAssistantId($paymentFromPosting['assistant_id']);
            }
            return $this;
        }

        // This is used while creating an order from the frontend by a customer.
        $inputParams = $this->inputParamsResolver->resolve();

        foreach ($inputParams as $inputParam) {
            if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                $additionalData = $inputParam->getData('additional_data');
                if (isset($additionalData['assistant_id'])) {
                    $payment->setAssistantId($additionalData['assistant_id']);
                    break;
                }
            }
        }

        return $this;
    }
}
