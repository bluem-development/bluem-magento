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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Framework\App;
use Magento\Framework\App\RequestInterface;

class SalesOrderPaymentBeforeSavedObserver implements ObserverInterface
{
    /**
     * @var InputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @param InputParamsResolver $inputParamsResolver
     * @param RequestInterface $requestInterface
     */
    public function __construct(
        InputParamsResolver $inputParamsResolver,
        RequestInterface $requestInterface
    ) {
        $this->inputParamsResolver = $inputParamsResolver;
        $this->requestInterface = $requestInterface;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        return $this;

        $payment_methods = [
            'epayment',
            'epayment-paypal',
            'epayment-creditcard',
            'epayment-cartebancaire',
            'epayment-sofort'
        ];
        
        $payment = $observer->getEvent()->getPayment();
        if (empty($payment)) {
            return $this;
        }

        if (!in_array($payment->getMethod(), $payment_methods)) {
            return $this;
        }

        if ($payment->getAssistantId()) {
            return $this;
        }

        // This is used while creating an order from the backend by an administrator.
        // if ($this->requestInterface->getFullActionName() == 'sales_order_create_save') {
        //     $paymentFromPosting = $this->requestInterface->getParam('payment');
        //     if ($paymentFromPosting && isset($paymentFromPosting['assistant_id'])) {
        //         $payment->setAssistantId($paymentFromPosting['assistant_id']);
        //     }
        //     return $this;
        // }

        // // This is used while creating an order from the frontend by a customer.
        // $inputParams = $this->inputParamsResolver->resolve();

        // foreach ($inputParams as $inputParam) {
        //     if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
        //         $additionalData = $inputParam->getData('additional_data');
        //         if (isset($additionalData['assistant_id'])) {
        //             $payment->setAssistantId($additionalData['assistant_id']);
        //             break;
        //         }
        //     }
        // }

        return $this;
    }
}
