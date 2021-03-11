<?php

namespace Bluem\Integration\Controller\Payment;

use \Magento\Framework\App\ObjectManager;
use Bluem\Integration\Controller\BluemAction;
use \Magento\Sales\Model\Order;

require_once __DIR__ . '/../BluemAction.php';


class Response extends BluemAction
{
    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $debug = false;

        $order_id = (int) $this->getRequest()->getParam('order_id');
        if ($debug) {

            echo " ALLE GET REQ PARAMS: ";
            var_dump($this->getRequest()->getParams());
            echo "<HR>" ;
            var_dump($order_id);
        }

        if ($order_id=="" ||!is_numeric($order_id)) {
            echo " Order ID niet goed teruggekregen;";
            exit;
        }

        try {
            
            $objectManager = ObjectManager::getInstance();   
            $orderRepository = $objectManager->create('\Magento\Sales\Model\OrderRepository');    
            $order = $orderRepository->get($order_id);
        } catch(\Throwable $th) {
            echo "Fout: ".$th->getMessage();
            exit;
        }
        // var_dump($order);
        // die();
        $state = $order->getState(); 
        // var_dump($state);
    //     /*get customer details*/


    // https://meetanshi.com/blog/get-order-information-by-order-id-in-magento-2/
      
    //     $custLastName= $orders->getCustomerLastname();
    //     $custFirsrName= $orders->getCustomerFirstname();
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

        $request = $this->_getRequestByOrderId($order_id);
        
        // validate if transaction ID is present in requests table
        if ($request === false) {
            echo " No request found with order ID {$order_id}; you might need to reinitiate the request.";
            exit;
        }

        if ($debug) {
            echo "<BR> FROM DB: " ;
            var_dump($request->getData());
            echo "<HR>";
        }

        $payload = json_decode($request->getPayload());
        $transactionId = $request->getTransactionId();
        $entranceCode = $request->getEntranceCode();

        
        // perform status request 
        $statusResponse = $this->_bluem->PaymentStatus(
            $transactionId,
            $entranceCode
        );
        
        
        if (!$statusResponse->ReceivedResponse()) {
            echo "No response received; please try again:<br> ";
            echo $request->getTransactionUrl();
            exit;
        } 
        
        if (!isset($statusResponse->PaymentStatusUpdate->Status)) {
            echo "Invalid / no status received; please try again:<br> ";
            echo $request->getTransactionUrl();
            exit;
        }
        $statusCode = ($statusResponse->PaymentStatusUpdate->Status)."";
        
        if ($debug) {
            var_dump($statusResponse);
            echo "<HR>STATUS: <br>";
            var_dump($statusCode);
            echo "<br>";
            // die();
        }


            
        switch ($statusCode) {
        case 'Success':
            // echo "do what you need to do in case of success!";

            $curPayload =(object) json_decode($request->getPayload());
            // potentially add to the payload
            // @todo: makea function for this
            $payloadString = json_encode($curPayload);

            $this->_updateRequest(
                $request->getId(),
                [
                    'Status'=>"Status_".$statusCode,
                    'Payload'=>$payloadString
                ]
            );

            $order->setState(Order::STATE_PROCESSING);

            $redirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            $redirect->setUrl($this->_baseURL.'/checkout/onepage/success');
            return $redirect;

            // echo "<p>Thanks for verifying your identity. You can now go back and proceed to other areas of our shop: <a href='".$home_url."'>$home_url</a>";
            // header("Location: $home_url ");
            break;
        case 'New':
            // what to do with NEW?
        case 'Processing':
        case 'Pending':
            echo "do something when the request is still processing (for example tell the user to come back later to this page)";
            break;
        case 'Cancelled':
            echo "You have cancelled the procedure. Please try again or contact support.";
            break;
        case 'Open':
            // do something when the request has not yet been completed by the user, redirecting to the transactionURL again";
            //@todo  get cur transaction url
            echo "Your request is still in progress. Please complete it on this page:";
            echo $request->getTransactionUrl();
            break;
        case 'Expired':
            echo "Your request has expired. Please try again or contact support.";
            break;
        default:
            echo "Your request has encountered an unexpected status. Please try again or contact support.";
            break;
        }
    }








    //     $status = false;

    //     if ($status) {
    //         $this->_success();
    //     } else {
    //         $this->_cancel();
    //     }

    //     //return $this->resultPageFactory->create();
    // }


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
