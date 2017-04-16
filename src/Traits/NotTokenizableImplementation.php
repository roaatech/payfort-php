<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 10:58 AM
 */

namespace ItvisionSy\Payment\PayFort\Traits;

trait NotTokenizableImplementation
{

    /** @var callable */
    protected $successCallback;
    /** @var callable */
    protected $failedCallback;

    /**
     * @return callable
     */
    public function getSuccessCallback()
    {
        return $this->successCallback;
    }

    /**
     * @param callable $success
     * @return Authorize
     */
    public function setSuccessCallback(callable $success)
    {
        $this->successCallback = $success;
        return $this;
    }

    /**
     * @return callable
     */
    public function getFailedCallback()
    {
        return $this->failedCallback;
    }

    /**
     * @param callable $failed
     * @return Authorize
     */
    public function setFailedCallback(callable $failed)
    {
        $this->failedCallback = $failed;
        return $this;
    }

    public static function execute($reference, callable $successCallback, callable $failedCallback, Config $config = null)
    {
        static::make($config)->setSuccessCallback($successCallback)->setFailedCallback($failedCallback)->process(['merchant_reference' => $reference]);
    }

    public function success(PaymentModel $model, $payfortReference, array $responseData)
    {
        $callable = $this->successCallback;
        if (!$callable) {
            throw new \Exception("Success callback should be set first using ->setSuccessCallbackCallback(callable) function");
        }
        return $callable($model, $payfortReference, $responseData);
    }

    public function failed($statusCode, $statusMessage, $responseCode, $responseMessage, $responseData, PaymentModel $paymentModel = null)
    {
        $callable = $this->failedCallback;
        if (!$callable) {
            throw new \Exception("Failed callback should be set first using ->setFailedCallbackCallback(callable) function");
        }
        return $callable($statusCode, $statusMessage, $responseCode, $responseMessage, $responseData, $paymentModel);
    }

}