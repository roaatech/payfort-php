<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 10:56 AM
 */

namespace ItvisionSy\Payment\PayFort\Contracts;

use ItvisionSy\Payment\PayFort\Config;

interface TokenizableImplementation
{
    /**
     * @return callable
     */
    public function getSuccessCallback();

    /**
     * @param callable $success
     * @return Authorize
     */
    public function setSuccessCallback(callable $success);

    /**
     * @return callable
     */
    public function getFailedCallback();

    /**
     * @param callable $failed
     * @return Authorize
     */
    public function setFailedCallback(callable $failed);

    /**
     * @param $requestData
     * @param callable $successCallback
     * @param callable $failedCallback
     * @param Config|null $config
     * @return mixed
     */
    public static function execute(array $requestData, callable $successCallback, callable $failedCallback, Config $config = null);

}