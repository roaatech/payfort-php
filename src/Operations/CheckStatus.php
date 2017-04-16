<?php

namespace ItvisionSy\Payment\PayFort\Operations;


use ItvisionSy\Payment\PayFort\AmountDecimals;
use ItvisionSy\Payment\PayFort\Config;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseCodeIdentifier;
use ItvisionSy\Payment\PayFort\Response\Identifiers\ResponseStatusIdentifier;
use ItvisionSy\Payment\PayFort\Sign;

abstract class CheckStatus extends NotTokenizableOperation
{

    /**
     * @param $reference
     * @param Config|null $config
     */
    public static function refund($reference, Config $config = null)
    {
        return static::make($config)->process(['merchant_reference' => $reference]);
    }

    public function command()
    {
        return "CHECK_STATUS";
    }

    public function process(array $data)
    {
        //retrieve information
        $reference = $this->getMerchantReference($data);
        $model = $this->getPaymentModel($reference);

        //prepare real API request data
        $requestData = [
            'merchant_identifier' => $this->config->getMerchantIdentifier(),
            'query_command' => $this->command(),
            'access_code' => $this->config->getAccessCode(),
            'language' => $this->config->getLanguage(),
            'merchant_reference' => $reference,
        ];

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
            return $this->success($model, $this->getFortReference($apiResponseData), ['api_response_data' => $apiResponseData]);
        } else {
            //failed
            return $this->failed($status, ResponseStatusIdentifier::name($status), ResponseCodeIdentifier::getStatusPart($responseCode), $responseMessage, $apiResponseData, $model);
        }
    }

    protected function getFortReference(array $responseData)
    {
        return $responseData['fort_id'];
    }
}