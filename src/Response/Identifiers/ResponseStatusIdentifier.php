<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 02/01/2017
 * Time: 2:10 PM
 */

namespace ItvisionSy\Payment\PayFort\Response\Identifiers;

class ResponseStatusIdentifier
{
    protected static $statuses = [
        0 => "Invalid Request.",
        1 => "Order Stored.",
        2 => "Authorization Success.",
        3 => "Authorization Failed.",
        4 => "Capture Success.",
        5 => "Capture failed.",
        6 => "Refund Success.",
        7 => "Refund Failed.",
        8 => "Authorization Voided Successfully.",
        9 => "Authorization Void Failed.",
        10 => "Incomplete.",
        11 => "Check status Failed.",
        12 => "Check status success.",
        13 => "Purchase Failure.",
        14 => "Purchase Success.",
        15 => "Uncertain Transaction.",
        17 => "Tokenization failed.",
        18 => "Tokenization success.",
        19 => "Transaction pending.",
        20 => "On hold.",
        21 => "SDK Token creation failure.",
        22 => "SDK Token creation success.",
        23 => "Failed to process Digital Wallet service.",
        24 => "Digital wallet order processed successfully.",
        27 => "Check card balance failed.",
        28 => "Check card balance success.",
        29 => "Redemption failed.",
        30 => "Redemption success.",
        31 => "Reverse Redemption transaction failed.",
        32 => "Reverse Redemption transaction success.",
        40 => "Transaction In review.",
        42 => "currency conversion success.",
        43 => "currency conversion failed."
    ];

    protected static $types = [
        "success" => [
            2, 4, 6, 8, 12, 14, 18, 22, 24, 28, 30, 32, 42
        ],
        "hold" => [
            20
        ]
    ];

    /**
     * @param int $status
     * @return string|null
     */
    public static function name($status)
    {
        return array_key_exists((int)$status, static::$statuses) ? static::$statuses[(int)$status] : null;
    }

    /**
     * @param int $status
     * @return bool
     */
    public static function isSuccess($status)
    {
        return array_search((int)$status, static::$types['success']) !== false;
    }

    /**
     * @param int $status
     * @return bool
     */
    public static function isPending($status)
    {
        return array_search((int)$status, static::$types['hold']) !== false;
    }

    /**
     * @param int $status
     * @return bool
     */
    public static function isFailure($status)
    {
        return !static::isSuccess($status) && !static::isPending($status);
    }

}