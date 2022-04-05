<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 * @author   Peter Meester <p.meester@bluem.nl>
 *
 */

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;
use Magento\Framework\App\ObjectManager;

require_once __DIR__ . '/../BluemAction.php';

class Request extends BluemAction
{
    public function execute()
    {
        $debug = false;

        $scenario = $this->_dataHelper->getIdentityConfig('identity_scenario');
        $requestCategories = $this->_dataHelper->getIdentityRequestCategories();

        // validate:
        // mandatory: if user is logged in (constraint for now)
        // optional: if user is not already verified?
        // Make a mention they can return to the previous page, create a view?

        // provide a link here to the callback function; either in this script or another script
        $returnURL = $this->_baseURL . "/bluem/identity/response/";

        $objectManager = ObjectManager::getInstance();
        $remote = $objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        $ip = $remote->getRemoteAddress();

        $payload = [
            'usecase' => $scenario,
            'categories' => $requestCategories,
            'ip' => $ip,
            'userdata' => []
        ];

        if ($this->_customerSession->isLoggedIn()) {
            $email = $this->_customerSession->getCustomer()->getEmail();
            $name = $this->_customerSession->getCustomer()->getName();
            $id = $this->_customerSession->getCustomer()->getId();

            $payload['userdata'] = [
                'email' => $email,
                'name' => $name,
                'id' => $id
            ];

            // description is shown to customer
            $description = "Verificatie {$name} (klantnummer {$id})";
            // client reference/number
            $debtorReference = "{$id}";
        } else {
            // guest user payload
            // @todo: add guest user data such as IP
            $payload['userdata'] = [
                'ip' => $ip." (guest)"
            ]; 
            
            // description is shown to customer
            $description = "Verificatie identiteit";
            // client reference/number
            $debtorReference = "Gastidentificatie";
        }

        $request_data = [
            'Type' => "identity",
            'Description' => $description,
            'DebtorReference' => $debtorReference,
            'ResponseUrl' => $returnURL,
            'ReturnUrl' => $returnURL,
            'Payload' => json_encode($payload),
            'Status' => "created"
        ];

        $request_db_id = parent::_createRequest(
            $request_data
        );

        // append created requestID to the URL to return to
        $returnURL .= "requestId/{$request_db_id}";

        $entranceCode = "";
        // @todo: uniquely generated ENTRANCECODE
        
        // Add return URL as response URL
        // After that, user will be redirected to return URL
        // Return URL can be edited later to go to specific page after processing
        $responseURL = $returnURL;

        if (!empty($_GET['returnurl'])) {
            $returnURL = $_GET['returnurl'];
        }

        $request = $this->_bluem->CreateIdentityRequest(
            $requestCategories,
            $description,
            $debtorReference,
            $entranceCode,
            $responseURL
        );

        $bluem_env = $this->_dataHelper->getGeneralConfig('environment');
        if ($bluem_env === "test") {
            $request->enableStatusGUI();
        }

        if ($debug) {
            var_dump("Request", $request);
            var_dump("Request URL", $request->HttpRequestURL());
            var_dump("Request XML", $request->XmlString());
        }

        $response = $this->_bluem->PerformRequest($request);

        if ($response->ReceivedResponse()) {
            if (isset($response->IdentityTransactionResponse->Error)) {
                echo $this->_getErrorMessageHtml(
                    $response->IdentityTransactionResponse->Error->ErrorMessage
                );
                exit;
            }

            $entranceCode = $response->getEntranceCode();
            $transactionID = $response->getTransactionID();
            $transactionURL = $response->getTransactionURL();

            if ($debug) {
                echo "entranceCode: {$entranceCode}<br>";
                echo "transactionID: {$transactionID}<br>";
                echo "<HR>RESPONSE:";
                var_dump($response);
            }
            // todo: add ageverify type
            // save this somewhere in your data store
            $update_data = [
                'EntranceCode' => $entranceCode,
                'TransactionId' => $transactionID,
                'TransactionUrl' => $transactionURL,
                'ResponseUrl' => $responseURL,
                'ReturnUrl' => $returnURL,
                'Status' => "requested"
            ];
            $this->_updateRequest($request_db_id, $update_data);
            if ($debug) {
                die();
            }
            // direct the user to this place
            header("Location: " . $transactionURL);
            exit;
        } else {
            echo $this->_getErrorMessageHtml($response);
            exit;
        }
        exit;
    }
}
