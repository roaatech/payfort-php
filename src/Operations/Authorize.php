<?php

namespace ItvisionSy\Payment\PayFort\Operations;

use ItvisionSy\Payment\PayFort\AmountDecimals;
use ItvisionSy\Payment\PayFort\Exceptions\InvalidResponseStructure;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseCodeIdentifier;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseStatusIdentifier;
use ItvisionSy\Payment\PayFort\Sign;

abstract class Authorize extends TokenizableOperation {

    public function command() {
        return "AUTHORIZATION";
    }

    public function process(array $responseData) {
        //retrieve information
        $reference = $this->getMerchantReference($responseData);
        $model = $this->getPaymentModel($reference);

        //prepare real API request data
        $apiRequestData = $this->getApiData($responseData, $reference, $model);

        //send the request
        $apiResponseData = $this->callApi($apiRequestData);

        //get the status and response code and data
        $status = @$apiResponseData['status'];
        $responseCode = @$apiResponseData['response_code'];
        $responseMessage = @$apiResponseData['response_message'];

        //check the response
        if (ResponseStatusIdentifier::isSuccess($status)) {
            //done
            return $this->success($model, $this->getFortReference($apiResponseData), ['api_response_data' => $apiResponseData]);
        } elseif (ResponseStatusIdentifier::isPending($status) && ResponseCodeIdentifier::isPending($responseCode)) {
            //3ds is required. This will return redirect again and then return back to the website.
            // Should be handled from the handle method.
            if (!array_key_exists('3ds_url', $apiResponseData)) {
                throw new InvalidResponseStructure();
            }
            return $this->complete3dSecurity($apiResponseData);
        } else {
            //failed
            return $this->failed($status, ResponseStatusIdentifier::name($status), ResponseCodeIdentifier::getStatusPart($responseCode), $responseMessage, $apiResponseData, $model);
        }
    }

    protected function complete3dSecurity($responseData) {
        //redirect to the 3rd security URL.
        return header("location:" . @$responseData['3ds_url']);
    }

    protected function getFortReference(array $responseData) {
        return $responseData['fort_id'];
    }

    protected function getApiData($responseData, $reference, $model) {
        $apiRequestData = [
            'command' => $this->command(),
            'merchant_identifier' => $this->config->getMerchantIdentifier(),
            'access_code' => $this->config->getAccessCode(),
            'customer_email' => $model->customerEmail(),
            'currency' => strtoupper($model->currency()),
            'amount' => AmountDecimals::forRequest($model->amount(), $model->currency()),
            'language' => $this->config->getLanguage(),
            'token_name' => $responseData['token_name'],
            'merchant_reference' => $reference,
            'customer_name' => $model->customerName(),
            'customer_ip' => $_SERVER['REMOTE_ADDR']
                ] + ($model->description() ? ['order_description' => $model->description()] : []) + (@$responseData['card_security_code'] ? ['card_security_code' => $responseData['card_security_code']] : []);

        //remove the customer IP if the IP is not correct. i.e. IPv6 or localhost
        if (!filter_var($apiRequestData['customer_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            unset($apiRequestData['customer_ip']);
        }

        //calculate request signature
        $apiRequestData['signature'] = Sign::make($this->config)->forRequest($apiRequestData);

        return $apiRequestData;
    }

}
