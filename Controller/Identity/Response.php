<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 *
 */

namespace Bluem\Integration\Controller\Identity;

use Bluem\Integration\Controller\BluemAction;

require_once __DIR__ . '/../BluemAction.php';

/**
 * Identity response controller function
 */
class Response extends BluemAction
{
    public function execute()
    {
        $debug = false;

        // get Transaction ID from get params;
        $requestId = (int) $this->getRequest()->getParam('requestId');
        if ($debug) {
            echo " ALLE GET REQ PARAMS: <BR>";
            print_r($this->getRequest()->getParams());
            echo "<HR>" ;
            var_dump($requestId);
        }

        if ($requestId=="" ||!is_numeric($requestId)) {
            echo $this->_getErrorMessageHtml(" Request ID niet goed teruggekregen","Kon verzoek niet aanmaken",true);
            exit;
        }

        $request_db_obj = $this->_getRequestByRequestId($requestId);
        if ($request_db_obj === false) {
            echo $this->_getErrorMessageHtml(" NO DB ITEM FOUND with ID {$requestId}","Kon verzoek niet aanmaken",true);
            exit;
        }

        if ($debug) {
            echo "<BR> FROM DB: " ;
            var_dump($request_db_obj->getData());
            echo "<HR>";
        }

        $transactionId = $request_db_obj->getTransactionId();
        $entranceCode = $request_db_obj->getEntranceCode();
        $responseUrl = $request_db_obj->getResponseUrl();
        $returnUrl = $request_db_obj->getReturnUrl();

        // perform status request
        $statusResponse = $this->_bluem->IdentityStatus(
            $transactionId,
            $entranceCode
        );

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
                // retrieve a report that contains the information based on the request type:
                $identityReport = $statusResponse->GetIdentityReport();

                // this contains an object with key-value pairs of relevant data from the bank:
                if ($debug) {
                    var_dump($identityReport);
                }
                $curPayload = (object) json_decode($request_db_obj->getPayload());
                $curPayload->report = $identityReport;

                $payloadString = json_encode($curPayload);

                $this->_updateRequest(
                    $requestId,
                    [
                        'Payload' => $payloadString
                    ]
                );

                // store that information and process it.
                if ($debug) {
                    echo "<HR>";
                }
                    
                if (!$debug && $returnUrl !== $responseUrl) {
                    // Redirect to return URL
                    header("Location: {$returnUrl}");
                } else {
                    // You can for example use the BirthDateResponse to determine the age of the user and act accordingly
                    $this->_showMiniPrompt(
                        "&#10003; Thanks for confirming your identity",
                        "<p>We have stored the relevant details.
                        You can now go back to our website.</p>"
                    );
                }
                break;
            case 'Processing':
            case 'Pending':
                $this->_showMiniPrompt(
                    "The request is still processing",
                    "Please come back later"
                );
                break;
            case 'Cancelled':
                $this->_showMiniPrompt(
                    "You have cancelled the procedure.",
                    " <a href='{$this->_baseURL}bluem/identity/request'>Please try again</a>."
                );
                break;
            case 'Open':
                // do something when the request has not
                // yet been completed by the user,
                // redirecting to the transactionURL again";
                $this->_showMiniPrompt(
                    "Your request is still in progress",
                    "Please complete it on the previous page."
                );
                break;
            case 'Expired':
                $this->_showMiniPrompt(
                    "Your request has expired",
                    "<a href='{$this->_baseURL}bluem/identity/request'>
                        Please try again
                    </a>."
                );
                break;
            default:
                $this->_showMiniPrompt(
                    "Your request has encountered an unexpected status",
                    "<a href='{$this->_baseURL}bluem/identity/request'>
                        Please try again
                    </a>."
                );
                break;
            }
        } else {
            // no proper response received, tell the user
            $this->_showMiniPrompt(
                "No valid response from Bluem service",
                "Please contact the webshop support"
            );
        }
        exit;
    }
}
