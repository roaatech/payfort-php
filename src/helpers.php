<?php

if (!function_exists('json')) {

    function json(array $data) {
        header("Content-Type:application/json; charset=UTF-8");
        echo json_encode($data);
        die;
    }

}

if (!function_exists('dd')) {

    function dd() {
        foreach (func_get_args() as $value) {
            var_dump($value);
        }
        die;
    }

}

if (!function_exists('payfort_config')) {

    /**
     * @param \ItvisionSy\Payment\PayFort\Config|array|string $config either a config object, or params for config object, or path to a config file
     * @return $this|\ItvisionSy\Payment\PayFort\Config
     * @throws Exception
     */
    function payfort_config($config = null) {
        if (!$config) {
            if (!array_key_exists('payfort_config', $GLOBALS) || empty($GLOBALS['payfort_config'])) {
                $file = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "payfort_config.php";
                $config = \ItvisionSy\Payment\PayFort\Config::make(file_exists($file) ? include($file) : \ItvisionSy\Payment\PayFort\Config::defaultConfig());
            } else {
                $config = payfort_config($GLOBALS['payfort_config']);
            }
            $GLOBALS['payfort_config'] = $config;
        } elseif (is_array($config)) {
            $config = \ItvisionSy\Payment\PayFort\Config::make($config);
        } elseif (is_string($config) && file_exists($config)) {
            $config = \ItvisionSy\Payment\PayFort\Config::make(require_once($config));
        } elseif (is_object($config) && $config instanceof \ItvisionSy\Payment\PayFort\Config) {
            //nothing is required
        } else {
            throw new Exception("Passed values for config can not be recognized as a valid config parameter.");
        }
        return $config;
    }

}

if (!function_exists('session_handler')) {

    /**
     * @param array|\ItvisionSy\Payment\PayFort\Contracts\SessionHandler $handler
     * @return \ItvisionSy\Payment\PayFort\SessionHandler
     */
    function session_handler($handler = null) {
        if (!$handler) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $handler = $_SESSION;
        }
        if (is_object($handler) && $handler instanceof \ItvisionSy\Payment\PayFort\Contracts\SessionHandler) {
            return $handler;
        } else {
            return new \ItvisionSy\Payment\PayFort\SessionHandler(is_array($handler) ? $handler : $_SESSION);
        }
    }

}

/**
 *
 * @param string $reference
 * @return ItvisionSy\Payment\PayFort\Contracts\PaymentModel|TransactionModel|OrderModel
 */
function load_payment_model($reference) {
    $exploded = TransactionModel::explodeReference($reference);
    if (count($exploded) == 2) {
        return TransactionModel::retrieveByPK($exploded[1]);
    } else {
        return OrderModel::retrieveByReference($exploded[0], OrderModel::FETCH_ONE);
    }
}

function generateTokenName(TransactionModel $model) {
    $data = $model->id;
    $userId = $model->orderModel()->user_id;
    $key = null; //str_pad('', config('security.crypt.key_length'), $userId);
    $token = encryptString($data, $key);
    return $token;
}

function decryptTokenName($token, $userId) {
    $key = null; //str_pad('', config('security.crypt.key_length'), $userId);
    return decryptString($token, $key);
}

function encryptString($string, $key = null) {
    $iv = config('security.crypt.iv');
    $key = $key ?: config('security.crypt.key');
    $method = config('security.crypt.method');
    $encryptedMessage = base64_encode(@openssl_encrypt($string, $method, $key, 0, $iv));
    return $encryptedMessage;
}

function decryptString($string, $key = null) {
    $iv = config('security.crypt.iv');
    $key = $key ?: config('security.crypt.key');
    $method = config('security.crypt.method');
    $decryptedMessage = @openssl_decrypt(base64_decode($string), $method, $key, 0, $iv);
    return $decryptedMessage;
}
