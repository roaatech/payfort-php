<?php

namespace ItvisionSy\Payment\PayFort\Operations;

use Exception;
use ItvisionSy\Payment\PayFort\Contracts\PaymentModel;
use ItvisionSy\Payment\PayFort\CreditCard;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseCodeIdentifier;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseStatusIdentifier;
use ItvisionSy\Payment\PayFort\Tokenize;

abstract class TokenizableOperation extends Operation {

    /**
     * Creates a TOKENIZATION request to proceed with a PURCHASE or AUTHORIZATION requests later
     *
     * This will generate an HTML form and auto submit it to PayFort URL.
     * @param array $postData
     */
    public function tokenize(array $postData) {
        $reference = $this->getMerchantReference($postData);
        $model = $this->getPaymentModel($reference);
        $creditCard = $this->getCreditCard($postData);
        Tokenize::make($this->config)->tokenize($model, $creditCard);
    }

    /**
     * @param array $postData
     * @return $this|CreditCard|static
     */
    protected function getCreditCard(array $postData) {
        $card = $postData['card_number'];
        $holder = $postData['card_holder_name'];
        $expiry = $postData['card_expiry_year'] . $postData['card_expiry_month'];
        $cvv2 = $postData['card_security_code'];
        $creditCard = CreditCard::make($card, $holder, $expiry, $cvv2);
        return $creditCard;
    }

    /**
     * @param array $responseData
     * @return mixed
     * @throws Exception
     */
    public function handle(array $responseData) {
        $reference = $this->getMerchantReference($responseData);
        $model = $this->getPaymentModel($reference);
        $forceTokenized = @$responseData['command'] && !array_key_exists('status', $responseData);
        $status = (int) @$responseData['status']; //
        $responseCode = @$responseData['response_code'];
        $command = @$responseData['service_command'] ?: @$responseData['command'];

        if ($command && ($forceTokenized || !ResponseStatusIdentifier::isFailure($status))) {

            /*
             * the process is success. The status is either:
             *  1. Tokenization request returned back with the token
             *  2. Purchase/Authorization/... process is done
             */

            switch ($command) {
                case 'TOKENIZATION':
                    //tokenization finished, now should do the logic of purchase,
                    $tokenName = @$responseData['token_name'];
                    $this->tokenGenerated($tokenName, $model, $responseData);
                    return $this->process($responseData);
                    break;
                case $this->command():
                    //real operation done (mostly after 3rd security check).
                    return $this->success($model, $this->getFortReference($responseData), $responseData);
                    break;
            }
        } else {

            return $this->failed($status, ResponseStatusIdentifier::name($status), ResponseCodeIdentifier::getStatusPart($responseCode), ResponseCodeIdentifier::message($responseCode), $responseData, $model);
        }

        //throw new InvalidResponseStructure("Response does not seem of a valid type or structure.");
    }

    protected function tokenGenerated($tokenName, PaymentModel $paymentModel = null, array $responseData = null) {
        //by default, token should not be saved unless it is an authorize or recurrent payment
        //hence, nothing to do
    }

}
