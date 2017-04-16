<?php

namespace ItvisionSy\Payment\PayFort\Operations;

use ItvisionSy\Payment\PayFort\Config;
use ItvisionSy\Payment\PayFort\Contracts\OperationHandler as OperationHandlerContract;
use ItvisionSy\Payment\PayFort\Exceptions\InvalidResponseStructure;

abstract class Operation implements OperationHandlerContract
{

    /** @var Config */
    protected $config;

    /**
     * @param Config|null $config
     * @return static|$this|Operation|OperationHandler
     */
    public static function make(Config $config = null)
    {
        return new static($config);
    }

    function __construct(Config $config = null)
    {
        $config = payfort_config($config);
        $this->config = $config;
    }

    /**
     * @return string
     */
    protected function apiUrl()
    {
        return $this->config->isSandbox()
            ? "https://sbpaymentservices.payfort.com/FortAPI/paymentApi"
            : "https://paymentservices.payfort.com/FortAPI/paymentApi";
    }

    /**
     * @return string service command of the operation. i.e. PURCHASE, AUTHORIZATION, TOKENIZATION, ...
     */
    abstract public function command();

    /**
     * Extract and return PayFort Reference for the command
     *
     * From command to another, the reference to check can be different. This should extract and return the correct
     *
     * @param array $responseData
     * @return string
     */
    abstract protected function getFortReference(array $responseData);


    /**
     * Performs a POST HTTP JSON request
     * @param array $data
     * @return array
     * @throws InvalidResponseStructure
     */
    protected function callApi(array $data)
    {

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;charset=UTF-8',
        ]);
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl()); //set the URL
        curl_setopt($ch, CURLOPT_POST, 1); //is a POST request
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); //stop on error
        curl_setopt($ch, CURLOPT_ENCODING, "compress, gzip"); //compress the request
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //no ssl verification
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //get the response into a variable
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); //the payload

        //execute the request
        $response = curl_exec($ch);

        //close the connection
        curl_close($ch);

        //parse the response
        $responseData = json_decode($response, true);

        //on empty response, throw an error
        if (!$response || empty($responseData)) {
            throw new InvalidResponseStructure();
        }

        //returned parsed data
        return $responseData;
    }

    /**
     * @param $reference
     * @return \ItvisionSy\Payment\PayFort\Contracts\PaymentModel
     * @throws \Exception
     */
    public function getPaymentModel($reference)
    {
        $model = $this->config->loadModelOrReturnFalse($reference);
        if ($model === false) {
            throw new \Exception("No model has been loaded. You need either to set the public payment model loader or to override this method");
        }
        return $model;
    }

    /**
     * The URL to redirect the operation to it after it is done from PayFort side.
     *
     * Usually, it is empty and should be set using the config different setResponseUrlXXX and response_url_xxx methods and configs
     * @return null|string
     */
    public function redirectUrl()
    {
        return null;
    }

    /**
     * @param array $postData
     * @return string
     * @throws Exception
     */
    public function getMerchantReference(array $postData)
    {
        if (!array_key_exists('merchant_reference', $postData)) {
            throw new Exception("Merchant reference was not passed");
        }
        return $postData['merchant_reference'];
    }

}