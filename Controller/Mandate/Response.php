<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Controller\Mandate;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;

use Bluem\Integration\Controller\BluemAction;
use Bluem\Integration\Helper\Data as DataHelper;
use Throwable;

class Response extends BluemAction
{
    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        DataHelper $dataHelper,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct(
            $context,
            $pageFactory,
            $dataHelper,
            $customerSession,
            $resourceConnection,
            $resultFactory
        );
        $this->_messageManager = $messageManager;
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $debug = false;

        $order_id = (int) $this->getRequest()->getParam('order_id');
        if ($debug) {
            echo " ALLE GET REQ PARAMS: ";
            var_dump($this->getRequest()->getParams());
            echo "<HR>order id: " ;
            var_dump($order_id);
        }

        if ($order_id=="" ||!is_numeric($order_id)) {
            echo "Fout: Order ID niet goed teruggekregen;";
            exit;
        }

        $objectManager = ObjectManager::getInstance();
        $orderRepository = $objectManager->create('\Magento\Sales\Model\OrderRepository');
        
        try {
            $order = $orderRepository->get($order_id);
        } catch (Throwable $th) {
            echo "Fout bij opvragen bestelling: ".$th->getMessage();
            exit;
        }

        $state = $order->getState();

        $request_db = $this->_getRequestByOrderId($order_id);
        // var_dump($request_db);
        // validate if transaction ID is present in requests table
        if ($request_db === false) {
            echo " No request found with order ID {$order_id}; you might need to initiate the request.";
            exit;
        }

        $payload = json_decode($request_db->getPayload());
        $transactionId = $request_db->getTransactionId();
        $entranceCode = $request_db->getEntranceCode();
        if ($debug) {
            echo "<BR>STATE: ";
            var_dump($state);
            echo "<BR> FROM DB: " ;
            var_dump($request_db->getData());
            echo "<HR>";
            var_dump($transactionId);
            var_dump($entranceCode);
            // die();
        }

        // validate TransactionId
        // validate EntranceCode
        // validate TransactionID and Entrance COde matching GET PARAMS


        
        // perform status request
        $statusResponse = $this->_bluem->MandateStatus(
            $transactionId,
            $entranceCode
        );
        
        
        if (!$statusResponse->ReceivedResponse()) {
            echo "No response received; please retry:<br> ";
            echo $request_db->getTransactionUrl();
            exit;
        }
        
        if (!isset($statusResponse->EMandateStatusUpdate->EMandateStatus->Status)) {
            echo "Invalid or no status received; please retry:<br> ";
            echo $request_db->getTransactionUrl();
            exit;
        }
        $statusCode = ($statusResponse->EMandateStatusUpdate->EMandateStatus->Status)."";
        
        if ($debug) {
            var_dump($statusResponse);
            echo "<HR>STATUS: <br>";
            var_dump($statusCode);
            echo "<br>";
            // die();
        }

        $redirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );

        switch ($statusCode) {
        case 'Success':
            // do what you need to do if successful

            $curPayload =(object) json_decode($request_db->getPayload());
            // potentially add to the payload
            
            $payloadString = json_encode($curPayload);

            $this->_updateRequest(
                $request_db->getId(),
                [
                    'Status'=>"Status_".$statusCode,
                    'Payload'=>$payloadString
                ]
            );

            $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
            $order->save();

            $redirect->setUrl($this->_baseURL.'/checkout/onepage/success');
            // echo "<p>Thanks for verifying your identity. You can now go back and proceed to other areas of our shop: <a href='".$home_url."'>$home_url</a>";
            // header("Location: $home_url ");
            return $redirect;
        case 'New':
            // what to do with NEW?
        case 'Processing':
        case 'Pending':
            echo "do something when the request is still processing (for example tell the user to come back later to this page)";
            // @todo do something when the request is still processing (for example tell the user to come back later to this page)
            break;
        case 'Cancelled':
            $msg = "You have cancelled the procedure. Please try again or contact support.";
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
            case 'Open':
            // What to do when the request is not yet completed by the user
            // e.g.: redirect to the transactionURL.
            $msg = "Your request is still in progress. Please complete it on this page:";
            $msg .= $request_db->getTransactionUrl();
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
        case 'Expired':
            $msg = "Your request has expired. Please try again or contact support.";
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
        }
        $msg = "Your request has encountered an unexpected status. Please try again or contact support.";
        $this->_messageManager->addErrorMessage($msg);
        $redirect->setUrl(
            $this->_baseURL.'/checkout/onepage/failure'
        );
        return $redirect;
    }

    // private function _success()
    // {
    //     $this->messageManager->addError(__('Payment has been successful.'));
    //     $resultRedirect = $this->resultRedirectFactory->create();
    //     $resultRedirect->setPath('checkout/onepage/success');
    //     return $resultRedirect;
    //     //return $this->resultPageFactory->create();
    // }

    // private function _cancel()
    // {
    //     $this->messageManager->addError(__('Payment has been cancelled.'));
    //     /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
    //     //change order status to cancel
    //     $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
    //     if ($order) {
    //         $order->cancel();
    //         $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_CANCELED, __('Canceled by customer.'));
    //         $order->save();
    //     }

    //     $resultRedirect = $this->resultRedirectFactory->create();
    //     $resultRedirect->setPath('checkout/cart');
    //     return $resultRedirect;
    // }
}




//     /*get customer details*/
// https://meetanshi.com/blog/get-order-information-by-order-id-in-magento-2/

//     $custLastName= $orders->getCustomerLastname();
//     $custFirstName= $orders->getCustomerFirstname();
//     $ipaddress=$order->getRemoteIp();
//     $customer_email=$order->getCustomerEmail();
//     $customerid=$order->getCustomerId();

//     /* get Billing details */
//     $billingaddress=$order->getBillingAddress();
//     $billingcity=$billingaddress->getCity();
//     $billingstreet=$billingaddress->getStreet();
//     $billingpostcode=$billingaddress->getPostcode();
//     $billingtelephone=$billingaddress->getTelephone();
//     $billingstate_code=$billingaddress->getRegionCode();

//     /* get shipping details */

//     $shippingaddress=$order->getShippingAddress();
//     $shippingcity=$shippingaddress->getCity();
//     $shippingstreet=$shippingaddress->getStreet();
//     $shippingpostcode=$shippingaddress->getPostcode();
//     $shippingtelephone=$shippingaddress->getTelephone();
//     $shippingstate_code=$shippingaddress->getRegionCode();

//    /* get  total */

//     $tax_amount=$order->getTaxAmount();
//     $total=$order->getGrandTotal();
