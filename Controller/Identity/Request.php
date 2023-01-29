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
        $scenario = $this->_dataHelper->getIdentityConfig('identity_scenario');
        
        $requestCategories = $this->_dataHelper->getIdentityRequestCategories();
        
        if (isset($_GET['verify'])) {
            if ($_GET['verify'] === 'account') {
                $scenario = '9'; // Account verification
            }
        }

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
            
            $description = "Verificatie {$name} (klantnummer {$id})";
            
            $debtorReference = "{$id}";

            $payload['userdata'] = [
                'email' => $email,
                'name' => $name,
                'id' => $id
            ];
        } else {
            $description = "Verificatie identiteit";
            
            $debtorReference = "Gastidentificatie";
            
            $payload['userdata'] = [
                'ip' => $ip." (guest)"
            ];
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

        $this->_bluem->setConfig('brandID', $this->_dataHelper->getIdentityConfig('identity_brand_id'));

        $request = $this->_bluem->CreateIdentityRequest(
            $requestCategories,
            $description,
            $debtorReference,
            $entranceCode,
            $responseURL
        );

        $request->setBrandId($this->_dataHelper->getIdentityConfig('identity_brand_id'));

        $bluem_env = $this->_dataHelper->getGeneralConfig('environment');
        if ($bluem_env === "test") {
            $request->enableStatusGUI();
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
            
            $update_data = [
                'EntranceCode' => $entranceCode,
                'TransactionId' => $transactionID,
                'TransactionUrl' => $transactionURL,
                'ResponseUrl' => $responseURL,
                'ReturnUrl' => $returnURL,
                'Status' => "requested"
            ];
            $this->_updateRequest($request_db_id, $update_data);
            header("Location: " . $transactionURL);
            exit;
        } else {
            echo $this->_getErrorMessageHtml($response);
            exit;
        }
        exit;
    }
}
