<?php

namespace ItvisionSy\Payment\PayFort;

class AmountDecimals
{

    static protected $precisions = [
        'JOD' => 3,
        'KWD' => 3,
        'OMR' => 3,
        'TND' => 3,
        'BHD' => 3,
        'LYD' => 3,
        'IQD' => 3
    ];

    public static function forRequest($amount, $currency)
    {
        $amount = (float)$amount;
        $precision = array_key_exists(strtoupper($currency), static::$precisions) ? static::$precisions[$currency] : 2;
        return round($amount, $precision) * (pow(10, $precision));
    }

    public static function fromResponse($amount, $currency)
    {
        $amount = (float)$amount;
        $precision = array_key_exists(strtoupper($currency), static::$precisions) ? static::$precisions[$currency] : 2;
        return round($amount, $precision) / (pow(10, $precision));
    }

}