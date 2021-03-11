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

        // @todo: eventually, only execute via AJAX & POST

        // https://magento.stackexchange.com/questions/200583/magento2-how-to-get-last-order-id-in-payment-module-template-file
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkout_session = $objectManager->get('Magento\Checkout\Model\Session');
        $order = $checkout_session->getLastRealOrder();
        $orderId = (int) $order->getEntityId();
        $orderIncrementId =  $order->getIncrementId();

        // @todo: check status
        // if order is not paid yet

        // :: Float
        $amount = $order->getGrandTotal();
        $currency = "EUR";

        if ($this->_customerSession->isLoggedIn()) {
            $userEmail = $this->_customerSession->getCustomer()->getEmail();
            $userName = $this->_customerSession->getCustomer()->getName();
            $userId = $this->_customerSession->getCustomer()->getId();

            // description is shown to customer
            $description = "Order {$orderIncrementId} (klantnummer {$userId})";
            // client reference/number
            $debtorReference = "{$orderId}-{$userId}";
        } else {
            $description = "Order {$orderIncrementId} (gastbestelling)";
            $debtorReference = "{$orderId}-guest";
        }

        // $debtorReference = "{$userId}";
        // $returnURL .= "requestId/{$request_db_id}";
        $returnURL = $this->_baseURL
            . "/bluem/payment/response/order_id/{$orderId}";


        try {
            $request = $this->_bluem->CreatePaymentRequest(
                $description,
                $debtorReference,
                $amount,
                null,
                $currency,
                null,
                $returnURL
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
            'user_email'        => $userEmail,
            'user_name'         => $userName,
            'order_id'          => $orderId,
            'order_increment_id'=>$orderIncrementId,
            'amount'            => $amount,
            'return_url'        => $returnURL,
            'currency'          => $currency
        ];

        /* Create request in database*/
        $request_db_data = [
            'Type'              => "payment",
            'Description'       => $description,
            'DebtorReference'   => $debtorReference,
            'OrderId'           => $orderId,
            'Payload'           => json_encode($payload),
            'Status'            => "created"
        ];

        $request_db_id = parent::_createRequest(
            $request_db_data
        );
        
        if ($response->ReceivedResponse()) {

            $transactionURL ="";

            $entranceCode   = $response->getEntranceCode();
            $transactionID  = $response->getTransactionID();
            $transactionURL = $response->getTransactionURL();

            $update_data = [
                'EntranceCode'      => $entranceCode,
                'TransactionId'     => $transactionID,
                'TransactionUrl'    => $transactionURL,
                'Status'            => "requested"
            ];
            parent::_updateRequest($request_db_id, $update_data);

            $result = [
                'error'             => false,
                'payment_url'       => $transactionURL,
                'description'       => $description,
                'debtorReference'   => $debtorReference,
                'amount'            => $amount,
                'returnURL'         => $returnURL,
                'payload'           => $payload
            ];
        } else {
            $result = [
                'error'    => true,
                'message'  => 'Could not create the Payment Request, no response received',
                'description'       => $description,
                'debtorReference'   => $debtorReference,
                'amount'            => $amount,
                'returnURL'         => $returnURL,
                'payload'           => $payload,
                'response'          => $response
            ];
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($result);
        exit;
    }
}
