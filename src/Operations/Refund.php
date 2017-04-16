<?php

namespace ItvisionSy\Payment\PayFort\Operations;

use ItvisionSy\Payment\PayFort\AmountDecimals;
use ItvisionSy\Payment\PayFort\Config;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseCodeIdentifier;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseStatusIdentifier;
use ItvisionSy\Payment\PayFort\Sign;

abstract class Refund extends NotTokenizableOperation {

    /**
     *
     * @param type $reference
     * @param type $payfortId
     * @param type $amount
     * @param type $currency
     * @param Config $config
     * @return mixed
     */
    public static function refund($reference, $payfortId, $amount, $currency = 'AED', Config $config = null) {
        return static::make($config)
                        ->process([
                            'merchant_reference' => $reference,
                            'fort_id' => $payfortId,
                            'amount' => $amount,
                            'currency' => $currency
        ]);
    }

    public function command() {
        return "REFUND";
    }

    public function process(array $data) {
        //retrieve information
        $reference = $this->getMerchantReference($data);
        $model = $this->getPaymentModel($reference);
        $amount = array_key_exists('amount', $data) ? $data['amount'] : $model->amount();
        $currency = array_key_exists('currency', $data) ? $data['currency'] : $model->currency();
        $fortId = array_key_exists('fort_id', $data) ? $data['fort_id'] : $model->payfortId();

        //prepare real API request data
        $requestData = [
            'merchant_identifier' => $this->config->getMerchantIdentifier(),
            'command' => $this->command(),
            'amount' => AmountDecimals::forRequest($amount, $currency),
            'access_code' => $this->config->getAccessCode(),
            'currency' => strtoupper($currency),
            'language' => $this->config->getLanguage(),
            'merchant_reference' => $reference,
            'fort_id' => $fortId] +
                ($model->description() ? ['order_description' => $model->description()] : []);

        //calculate request signature
        $requestData['signature'] = Sign::make($this->config)->forRequest($requestData);

        //send the request
        $apiResponseData = $this->callApi($requestData);

        //get the status and response code and data
        $status = @$apiResponseData['status'];
        $responseCode = @$apiResponseData['response_code'];
        $responseMessage = @$apiResponseData['response_message'];

        //check the response
        if (ResponseStatusIdentifier::isSuccess($status)) {
            //done
            return $this->success($model, $this->getFortReference($apiResponseData), $apiResponseData);
        } else {
            //failed
            return $this->failed($status, ResponseStatusIdentifier::name($status), ResponseCodeIdentifier::getStatusPart($responseCode), $responseMessage, $apiResponseData, $model);
        }
    }

    protected function getFortReference(array $responseData) {
        return $responseData['fort_id'];
    }

}
