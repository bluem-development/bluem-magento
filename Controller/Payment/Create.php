<?php

// BASED ON https://magento.stackexchange.com/questions/141974/custom-place-order-redirect-with-orderid


namespace Bluem\Integration\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use stdClass;

class Create extends Action
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        

        
        // $this->checkoutSession = $checkoutSession;
        // $order = $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Place Order action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $data = [
            'error' => true,
            'message' => __('Order ID no longer exist.')
        ];
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()
            && $this->getRequest()->getMethod() == 'POST'
        ) {

            // $orderId = (int)$this->getRequest()->getParam('order_id');
            // $orderObject = $this->orderFactory->create()->load($orderId);


            $paymentUrl = "https://google.com";
            if (true) {
                $data['error'] = false;
                $data['message'] = __('Retrieve the payment URL successfully.');
                $data['payment_url'] = $paymentUrl;
            }
        }

        return $result->setData($data);
    }

}