<?php

namespace ItvisionSy\Payment\PayFort\Contracts;

interface OperationHandler
{
    /**
     * Gets the payment model object from the merchant reference
     * @param $reference
     * @return PaymentModel
     */
    public function getPaymentModel($reference);

    /**
     * Gets the merchant reference from the posted data
     * @param array $postData
     * @return string
     */
    public function getMerchantReference(array $postData);

    /**
     * @param array $responseData
     * @return mixed
     * @throws \Exception
     */
    public function handle(array $responseData);

    public function process(array $data);

    public function success(PaymentModel $model, $payfortReference, array $responseData);

    public function failed($statusCode, $statusMessage, $responseCode, $responseMessage, $responseData, PaymentModel $paymentModel = null);

}