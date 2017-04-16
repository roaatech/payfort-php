<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 10:58 AM
 */

namespace ItvisionSy\Payment\PayFort\Traits;

trait TokenizableImplementation
{

    use NotTokenizableImplementation;

    public static function execute(array $postData, callable $successCallback, callable $failedCallback, Config $config = null)
    {
        static::make($config)->setSuccessCallback($successCallback)->setFailedCallback($failedCallback)->tokeniz($postData);
    }

}