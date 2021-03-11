<?php

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

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
        
        $returnURL = $this->_baseURL . "/bluem/identity/response/"; // provide a link here to the callback function; either in this script or another script
        

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $remote = $objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        $ip = $remote->getRemoteAddress();
        
        $payload = [
            'usecase'=>$scenario,
            'categories'=>$requestCategories,
            'ip'=>$ip,
            'userdata'=>[]
        ];
                

        if($this->_customerSession->isLoggedIn())
        {
            $email = $this->_customerSession->getCustomer()->getEmail();
            $name = $this->_customerSession->getCustomer()->getName();
            $id = $this->_customerSession->getCustomer()->getId();

            $payload['userdata'] = [
                'email'=> $email,
                'name'=> $name,
                'id'  => $id
            ];
            $description = "Verificatie $name (klantnummer $id)"; // description is shown to customer
            $debtorReference = "$id"; // client reference/number
        } else {
            $payload['userdata'] = [];
            $description = "Verificatie identiteit";
            $debtorReference = "";
        }

        $request_data = [
            'Type'=>"identity",
            'Description'=>$description,
            'DebtorReference'=>$debtorReference,
            'ReturnUrl'=>$returnURL,
            'Payload'=>json_encode($payload),
            'Status'=>"created"
        ];

        $request_db_id = parent::_createRequest(
            $request_data
        );
        
        // append created requestID to the URL to return to
        $returnURL .= "requestId/{$request_db_id}";

        $request = $this->_bluem->CreateIdentityRequest(
            $requestCategories,
            $description,
            $debtorReference,
            $returnURL
        );
        

        $response = $this->_bluem->PerformRequest($request);
        
        if ($response->ReceivedResponse()) {
            $entranceCode = $response->getEntranceCode();
            $transactionID = $response->getTransactionID();
            $transactionURL = $response->getTransactionURL();
            
            if($debug) {
                
                echo "entranceCode: {$entranceCode}<br>";
                echo "transactionID: {$transactionID}<br>";
                
            }
            // todo: add ageverify type
            // save this somewhere in your data store
            $update_data = [
                'EntranceCode'=>$entranceCode,
                'TransactionId'=>$transactionID,
                'TransactionUrl'=>$transactionURL,
                'ReturnUrl'=>$returnURL, // also updated this
                'Status'=>"requested"
            ];
            $this->_updateRequest($request_db_id, $update_data);
            
            
            // $_SESSION['entranceCode'] = $entranceCode;
            // $_SESSION['transactionID'] = $transactionID;
            // $_SESSION['transactionURL'] = $transactionURL;
            
            // direct the user to this place
            header("Location: ".$transactionURL);
            
              // .. or for now, just show the URL:
                // echo "<hr>TransactionURL: <a href='{$transactionURL}' target='_blank'>
                // $transactionURL
                // </a>";
                exit;
            } else {
                echo " Invalid response received, please contact your webshop administrator";
                exit;
                // no proper response received, tell the user
            }
            exit;
        }
    }
    