<?php

// BASED ON https://magento.stackexchange.com/questions/141974/custom-place-order-redirect-with-orderid


namespace Bluem\Integration\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;

class Create extends Action
{
    /** @var Session */
    private $checkoutSession;

    /**
     * @param Context $context
     * @param CreateHostedCheckout $hostedCheckout
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Initialize redirect to bank
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        // echo " HI" ; die();
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        /** do stuff here */
var_dump($order);
// die();
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('https://google.com');

        return $resultRedirect;
    }
}