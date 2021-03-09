<?php

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

require_once __DIR__ . '/../BluemAction.php';

class Response extends BluemAction
{
    
    public function execute()
    {
        $debug = false;
        // parse the callback functionality here. THis is done in one file for simplicity's sake. It is recommended to do this in a separate file
        
        
        // get Transaction ID from get params;
        
        // retrieve user ID and saved session information in database
      
        //in a controller
        $requestId = (int) $this->getRequest()->getParam('requestId');
        if($debug) {
            
            echo " ALLE GET REQS";
            var_dump($this->getRequest()->getParams());
            echo "<HR>" ;
            var_dump($requestId);
        }
        
        if($requestId=="" ||!is_numeric($requestId)) {
            echo " Request ID niet goed teruggekregen;";
            exit;
        }
        
        $request_db_obj = $this->_getRequestByRequestId($requestId);
        if($request_db_obj === false) {
            echo " NO DB ITEM FOUND with ID {$requestId}";
            exit;
        } 

        if($debug) {

            echo "<BR> FROM DB: " ;
            var_dump($request_db_obj->getData());
            echo "<HR>";
            
        }
        // exit;

        $transactionId = $request_db_obj->getTransactionId();
        $entranceCode = $request_db_obj->getEntranceCode();

        // validate if transaction ID is present in identity table
        
        // perform status request 
        
        $statusResponse = $this->_bluem->IdentityStatus(
            $transactionId,
            $entranceCode
        );
        
        // store status request
        
        
        // handle the status accordingly
        
        
        
        if ($statusResponse->ReceivedResponse()) {
            
            $statusCode = ($statusResponse->GetStatusCode());
            
            if ($debug) {
                echo "<HR>STATUS: {$statusCode}<br>";
            }
            

            $this->_updateRequest(
                $requestId,
                ['Status'=>'response_'.strtolower($statusCode)]
            );

switch ($statusCode) {
    case 'Success':
    // echo "do what you need to do in case of success!";
    
    // retrieve a report that contains the information based on the request type:
    $identityReport = $statusResponse->GetIdentityReport();
    
    // this contains an object with key-value pairs of relevant data from the bank:
    if($debug) {
        var_dump($identityReport);    
    }   
    $curPayload =(object) json_decode($request_db_obj->getPayload());
    $curPayload->report = $identityReport;

    $payloadString = json_encode($curPayload);

    $this->_updateRequest(
        $requestId,
        ['Payload'=>$payloadString]
    );

    // store that information and process it.
    if ($debug) {
        echo "<HR>";
    }
    $home_url = $this->_baseURL."";
    // You can for example use the BirthDateResponse to determine the age of the user and act accordingly
    echo "<p>Thanks for verifying your identity. You can now go back and proceed to other areas of our shop: <a href='".$home_url."'>$home_url</a>";
    // header("Location: $home_url ");
    break;
    case 'Processing':
        case 'Pending':
            echo "do something when the request is still processing (for example tell the user to come back later to this page)";
            break;
            case 'Cancelled':
            echo "You have cancelled the procedure. <a href='{$this->_baseURL}bluem/identity/request'>Please try again</a>.";
            break;
            case 'Open':
            // do something when the request has not yet been completed by the user, redirecting to the transactionURL again";
            //@todo  get cur transaction url
            echo "Your request is still in progress. Please complete it on this page:";
            break;
            case 'Expired':
            echo "Your request has expired. <a href='{$this->_baseURL}bluem/identity/request'>Please try again</a>.";
            break;
            default:
            echo "Your request has encountered an unexpected status. <a href='{$this->_baseURL}bluem/identity/request'>Please try again</a>.";
            break;
            }
        } else {
            // no proper response received, tell the user
            echo " No response from Bluem.
            Please contact webshop support";
        }
        exit;
    }
}
    