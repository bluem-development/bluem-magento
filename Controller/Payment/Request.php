<?php

namespace Bluem\Integration\Controller\Payment;

use Bluem\Integration\Controller\BluemAction;

use Carbon\Carbon;

use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\App\RequestInterface;

require_once __DIR__ . '/../BluemAction.php';

class Request extends BluemAction
{
    /**
     * Prints the Payment from informed order id
     *
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        // https://magento.stackexchange.com/questions/200583/magento2-how-to-get-last-order-id-in-payment-module-template-file
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkout_session = $objectManager->get('Magento\Checkout\Model\Session');
        $order = $checkout_session->getLastRealOrder();
        $order->getEntityId();
        $order_id =  $order->getIncrementId();

        // :: Float
        $amount = $order->getGrandTotal();


        if ($this->_customerSession->isLoggedIn()) {
            $email = $this->_customerSession->getCustomer()->getEmail();
            $name = $this->_customerSession->getCustomer()->getName();
            $id = $this->_customerSession->getCustomer()->getId();

            // description is shown to customer
            $description = "Order $order_id (klantnummer $id)"; 
            // client reference/number
            $debtorReference = "{$order_id}-{$id}"; 
        } else {
            $description = "Order $order_id (gastbestelling)";
            $debtorReference = "{$order_id}-gast";
        }

        $debtorReference = "{$id}";
        // $returnURL .= "requestId/{$request_db_id}";
        $returnURL = $this->_baseURL
            . "/bluem/payment/response/"
            . "?order_id={$order_id}";


        try {
            //code...
            $request = $this->_bluem->CreatePaymentRequest(
                $description,
                $debtorReference,
                $amount,
                null,
                "EUR",
                null
            );
            $response = $this->_bluem->PerformRequest($request);
        } catch (\Throwable $th) {
            $result = [
                'error' => true,
                'message' => 'Could not create the Payment Request, more details: '.
                    $th->getMessage()
            ];
        }

        $payload = [
            'order_id'=>$order_id,
            'amount'=> $amount
        ];

        /* Create request in database*/
        $request_data = [
            'Type'=>"payment",
            'Description'=>$description,
            'DebtorReference'=>$debtorReference,
            'ReturnUrl'=>$returnURL,
            'Payload'=>json_encode($payload),
            'Status'=>"created"
        ];

        $request_db_id = parent::_createRequest(
            $request_data
        );

        $transactionURL ="";
        if ($response->ReceivedResponse()) {
            $entranceCode = $response->getEntranceCode();
            $transactionID = $response->getTransactionID();
            $transactionURL = $response->getTransactionURL();


            $update_data = [
                'EntranceCode'=>$entranceCode,
                'TransactionId'=>$transactionID,
                'TransactionUrl'=>$transactionURL,
                'Status'=>"requested"
            ];
            parent::_updateRequest($request_db_id, $update_data);

            $result = [
                'error'=>false,
                'payment_url'=>$transactionURL
            ];
        } else {
            $result = [
                'error' => true,
                'message' => 'Could not create the Payment Request, no response received'
            ];
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($result);
        exit;
    }
}
