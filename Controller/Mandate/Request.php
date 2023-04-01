<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */
namespace Bluem\Integration\Controller\Mandate;

use Bluem\Integration\Controller\BluemAction;

use Carbon\Carbon;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Throwable;

require_once __DIR__ . '/../BluemAction.php';

class Request extends BluemAction
{
    /**
     * Prints the Mandate from informed order id
     *
     * @return Page
     * @throws LocalizedException
     */
    public function execute()
    {
        $debug = false;

        // https://magento.stackexchange.com/questions/200583/magento2-how-to-get-last-order-id-in-payment-module-template-file
        
        $objectManager = ObjectManager::getInstance();
        $checkout_session = $objectManager->get('Magento\Checkout\Model\Session');
        
        $order = $checkout_session->getLastRealOrder();
        
        $orderId = (int) $order->getEntityId();
        $orderIncrementId =  $order->getIncrementId();
        
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance()->getCode();

        $additional = $payment->getAdditionalInformation();

        $additionalData = $this->getRequest()->getParams();
        
        var_dump($additional, $additionalData);

        // :: Float
        $amount = $order->getGrandTotal();
        $currency = "EUR";

        $userEmail = "";
        $userId = "";
        $userName = "";

        // Check for recurring mode
        if ($this->_bluem->getConfig('sequenceType') === 'RCUR')
        {
            //
        }
        else
        {
            //
        }
        
        if ($this->_customerSession->isLoggedIn()) {
            $userEmail = $this->_customerSession->getCustomer()->getEmail();
            $userName = $this->_customerSession->getCustomer()->getName();
            $userId = $this->_customerSession->getCustomer()->getId();

            // description is shown to customer
            $description = "Order {$orderIncrementId} (klantnummer {$userId})";
            // client reference/number
            $debtorReference = "{$orderId}";
        } else {
            // guest order
            $description = "Order {$orderIncrementId} (gastbestelling)"; 
            $debtorReference = "{$orderId}";
        }

        $returnURL = $this->_baseURL
            . "/bluem/mandate/response/order_id/"
            . $orderId;

        $payload = [
            'user_id'           => $userId,
            'user_email'        => $userEmail,
            'user_name'         => $userName,
            'order_id'          => $orderId,
            'order_increment_id'=> $orderIncrementId,
            'selected_bank'     => $selectedBank,
            'amount'            => $amount,
            'return_url'        => $returnURL,
            'currency'          => $currency,
            'method'            => $method,
        ];

        $request_db = $this->_getRequestByOrderId($orderId);

        // validate if item is already present in requests table
        if ($request_db === false) {

            // Request does not exist yet

            /* Create request in database*/
            $request_db_data = [
                'Type'              => "mandate",
                'Description'       => $description,
                'DebtorReference'   => $debtorReference,
                'OrderId'           => $orderId,
                'Payload'           => json_encode($payload),
                'Status'            => "created"
            ];

            $request_db_id = parent::_createRequest(
                $request_db_data
            );
        } else {
            // request already exists,
            if ($debug) {
                echo "REQUEST ALREADY EXISTS, exiting";
                exit;
            }
            $request_db_id = $request_db->getId();

            $transactionUrl = $request_db->getTransactionUrl();
            if ($transactionUrl !=="") {
                $result = [
                    'error'       => false,
                    'payment_url' => $transactionUrl
                ];
            } else {
                $result = [
                    'error'       => true,
                    'message'     => "Kon transactie niet juist initialiseren"
                ];
            }
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($result);
            exit;
        }

        try {
            $payment_brand_id = 'mandate_brand_id';

            $this->_bluem->setConfig('brandID', $this->_dataHelper->getMandateConfig($payment_brand_id));

            $this->_bluem->setConfig('merchantReturnURLBase', $returnURL);

            $request = $this->_bluem->CreateMandateRequest(
                !empty($userId) ? $userId : $orderId,
                $orderId,
            );

            $request->setBrandId($this->_dataHelper->getMandateConfig($payment_brand_id));

            $response = $this->_bluem->PerformRequest($request);
        } catch (Throwable $th) {
            $result = [
                'error' => true,
                'message' => 'Could not create the Payment Request, more details: '.
                    $th->getMessage()
            ];

            header("Content-type: application/json; charset=utf-8");
            echo json_encode($result);
            exit;
        }

        if ($response->ReceivedResponse()) {
            $transactionURL ="";

            $entranceCode   = $request->entranceCode;
            $transactionID  = $request->mandateID;
            $transactionURL = $response->getTransactionURL();

            // Save the necessary data to later request more information and refer to this transaction
            $_SESSION['bluem_mandateId'] = $request->mandateID;
            $_SESSION['bluem_entranceCode'] = $request->entranceCode;

            $update_data = [
                'EntranceCode'      => $entranceCode,
                'TransactionId'     => $transactionID,
                'TransactionUrl'    => $transactionURL,
                'Status'            => "requested"
            ];
            // var_dump($request_db_id);
            // var_dump($updated);
            // die();
            $updated= parent::_updateRequest($request_db_id, $update_data);
            $result = [
                'error'             => false,
                'payment_url'       => $transactionURL,
                'description'       => $description,
                'debtorReference'   => $debtorReference,
                'amount'            => $amount,
                'returnURL'         => $returnURL,
                'payload'           => $payload,
                'EntranceCode'      => $entranceCode,
                'TransactionId'     => $transactionID,
                'TransactionUrl'    => $transactionURL,
                'Status'            => "requested"
            ];
        } else {
            $result = [
                'error'    => true,
                'message'  => 'Could not create the Mandate Request: no response received'
            ];
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($result);
        exit;
    }
}
