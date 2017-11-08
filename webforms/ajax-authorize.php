<?php

require_once ABSPATH . '../vendor/autoload.php';

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

/**
 * Profile Form - AJAX Authorize Transaction
 *
 */
function authorizeTransaction() 
{ 
    // Security
    check_ajax_referer('webform-script-nonce', '_security_nonce');

    // Variables
    $results = array();
    $results['errorFields'] = array();

    // Check for missing fields.
    foreach ($_POST as $key => $value) {
        if (empty($value)) {
            $results['errorFields'][$key] = $key . ' is required.';
        }
    }

    // Return Error.
    if (!empty($results['errorFields'])) {
        $results['success'] = false;
        $results['errorMessage'] = 'All fields are required.';
        echo json_encode($results);
        return;
    }

    // Extract to variables.
    extract($_POST);

    // Create a merchantAuthenticationType object with authentication details retrieved from the constants file.
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName('6ENM26hn24');
    $merchantAuthentication->setTransactionKey('247W8J8Yv3YJv6m2');

    // $merchantAuthentication->setName('6q7ApZy9rt'); << Production
    // $merchantAuthentication->setTransactionKey('9qEf737S6y579EJH'); << Production
    
    // Set the transaction's refId
    $refId = 'ref' . time();

    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($cardNumber);
    $creditCard->setExpirationDate($cardExpiration);
    $creditCard->setCardCode($cvc);

    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setInvoiceNumber($member_id);
    $order->setDescription($membership . ' Membership');

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
    $customerAddress->setFirstName($firstName);
    $customerAddress->setLastName($lastName);
    $customerAddress->setAddress($address);
    $customerAddress->setCity($city);
    $customerAddress->setState($state);
    $customerAddress->setZip($zip);
    $customerAddress->setCountry("USA");
    $customerAddress->setPhoneNumber($phone);

    // Set the customer's identifying information
    $customerData = new AnetAPI\CustomerDataType();
    $customerData->setType("individual");
    $customerData->setId($member_id);
    $customerData->setEmail($email);

    // Add values for transaction settings
    $duplicateWindowSetting = new AnetAPI\SettingType();
    $duplicateWindowSetting->setSettingName("duplicateWindow");
    $duplicateWindowSetting->setSettingValue("60");

    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount($total);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setBillTo($customerAddress);
    $transactionRequestType->setCustomer($customerData);
    $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);

    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);

    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    $response   = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    // $response   = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION); <<- Production
    
    // Response
    if ($response != null) {

        // Check to see if the API request was successfully received and acted upon
        if ($response->getMessages()->getResultCode() == 'Ok') {

            // Since the API request was successful, look for a transaction response
            // and parse it to display the results of authorizing the card
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getMessages() != null) {
                $results = array(
                    'success'       => true,
                    'transactionId' => $tresponse->getTransId(),
                    'reponseCode'   => $tresponse->getResponseCode(),
                    'messageCode'   => $tresponse->getMessages()[0]->getCode(),
                    'authMethod'    => $tresponse->getAccountType() . ' ' . $tresponse->getAccountNumber(),
                    'authCode'      => $tresponse->getAuthCode(),
                    'description'   => $tresponse->getMessages()[0]->getDescription()
                );
            } else {

                $results['success'] = false;

                if ($tresponse->getErrors() != null) {
                    $results['errorCode']    = $tresponse->getErrors()[0]->getErrorCode();
                    $results['errorMessage'] = $tresponse->getErrors()[0]->getErrorText();
                }
            }
            // Or, print errors if the API request wasn't successful

        } else {
            $results['success'] = false;
            $tresponse = $response->getTransactionResponse();
        
            if ($tresponse != null && $tresponse->getErrors() != null) {
                $results['errorCode']    = $tresponse->getErrors()[0]->getErrorCode();
                $results['errorMessage'] = $tresponse->getErrors()[0]->getErrorText();
            } else {
                $results['errorCode']    = $response->getMessages()->getMessage()[0]->getCode();
                $results['errorMessage'] = $response->getMessages()->getMessage()[0]->getText();
            }
        }
    } else {
        $results['success'] = false;
        $results['errorMessage'] = 'No response results';
    }

    // Response output in JSON Representation
    header('Content-Type: application/json');
    echo json_encode($results);

    // End AJAX Request.
    wp_die();
}
add_action('wp_ajax_authorize_transation', 'authorizeTransaction');
add_action('wp_ajax_nopriv_authorize_transation', 'authorizeTransaction');
