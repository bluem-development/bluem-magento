<?php

namespace Bluem\Integration\Controller\Payment;

use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\Order;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\Action\Context;
use \Magento\Customer\Model\Session;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Framework\Controller\ResultFactory;
// require_once __DIR__ . '/../BluemAction.php';
use Bluem\Integration\Controller\BluemAction;
use Bluem\Integration\Helper\Data as DataHelper;

class Response extends BluemAction
{

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
     * @return \Magento\Framework\Controller\ResultInterface
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
        } catch(\Throwable $th) {
            echo "Fout bij opvragen bestelling: ".$th->getMessage();
            exit;
        }
        // var_dump($order);
        // die();
        $state = $order->getState(); 
        
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

        $request_db = $this->_getRequestByOrderId($order_id);
        // var_dump($request_db);
        // validate if transaction ID is present in requests table
        if ($request_db === false) {
            echo " No request found with order ID {$order_id}; you might need to reinitiate the request.";
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
        $statusResponse = $this->_bluem->PaymentStatus(
            $transactionId,
            $entranceCode
        );
        
        
        if (!$statusResponse->ReceivedResponse()) {
            echo "No response received; please try again:<br> ";
            echo $request_db->getTransactionUrl();
            exit;
        } 
        
        if (!isset($statusResponse->PaymentStatusUpdate->Status)) {
            echo "Invalid / no status received; please try again:<br> ";
            echo $request_db->getTransactionUrl();
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

        $redirect = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
        );


        switch ($statusCode) {
        case 'Success':
            // echo "do what you need to do in case of success!";

            $curPayload =(object) json_decode($request_db->getPayload());
            // potentially add to the payload
            // @todo: makea function for this
            $payloadString = json_encode($curPayload);

            $this->_updateRequest(
                $request_db->getId(),
                [
                    'Status'=>"Status_".$statusCode,
                    'Payload'=>$payloadString
                ]
            );

            // $order->setState(Order::STATE_PROCESSING);
            $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
            $order->save();

            // echo "HIER";
            // die();
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
            $msg = "You have cancelled the procedure. Please try again or contact support.";
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
            // return $resultRedirect->setPath('checkout/onepage/failure');
            break;
        case 'Open':
            // do something when the request has not yet been completed by the user, redirecting to the transactionURL again";
            //@todo  get cur transaction url
            $msg = "Your request is still in progress. Please complete it on this page:";
            $msg .= $request_db->getTransactionUrl();
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
            break;
        case 'Expired':
            $msg = "Your request has expired. Please try again or contact support.";
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
            break;
        default:
            $msg = "Your request has encountered an unexpected status. Please try again or contact support.";
            $this->_messageManager->addErrorMessage($msg);
            $redirect->setUrl(
                $this->_baseURL.'/checkout/onepage/failure'
            );
            return $redirect;
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
