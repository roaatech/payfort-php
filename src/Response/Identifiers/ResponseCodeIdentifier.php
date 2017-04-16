<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 02/01/2017
 * Time: 2:26 PM
 */

namespace ItvisionSy\Payment\PayFort\Response\Identifiers;

class ResponseCodeIdentifier
{
    protected static $codes = [
        0 => "Success.",
        1 => "Missing parameter.",
        2 => "Invalid parameter format.",
        3 => "Payment option is not available for this merchant’s account.",
        4 => "Invalid command.",
        5 => "Invalid amount.",
        6 => "Technical problem.",
        7 => "Duplicate order number.",
        8 => "Signature mismatch.",
        9 => "Invalid merchant identifier.",
        10 => "Invalid access code.",
        11 => "Order not saved.",
        12 => "Card expired.",
        13 => "Invalid currency.",
        14 => "Inactive payment option.",
        15 => "Inactive merchant account.",
        16 => "Invalid card number.",
        17 => "Operation not allowed by the acquirer.",
        18 => "Operation not allowed by processor.",
        19 => "Inactive acquirer.",
        20 => "Processor is inactive.",
        21 => "Payment option deactivated by acquirer.",
        22 => "Payment option deactivated by processor.",
        23 => "Currency not accepted by acquirer.",
        24 => "Currency not accepted by processor.",
        25 => "Processor integration settings are missing.",
        26 => "Acquirer integration settings are missing.",
        27 => "Invalid extra parameters.",
        28 => "Missing operations settings. Contact PAYFORT operations support.",
        29 => "Insufficient funds.",
        30 => "Authentication failed.",
        31 => "Invalid issuer.",
        32 => "Invalid parameter length.",
        33 => "Parameter value not allowed.",
        34 => "Operation not allowed.",
        35 => "Order created successfully.",
        36 => "Order not found.",
        38 => "Tokenization service inactive.",
        40 => "Invalid transaction source as it does not match the Origin URL or the Origin IP.",
        42 => "Operation amount exceeds the authorized amount.",
        43 => "Inactive Operation.",
        44 => "Token name does not exist.",
        45 => "Merchant does not have the Token service and yet “token_name” was sent.",
        46 => "Channel is not configured for the selected payment option.",
        47 => "Order already processed.",
        48 => "Operation amount exceeds the captured amount.",
        49 => "Operation not valid for this payment option.",
        50 => "Merchant per transaction limit exceeded.",
        51 => "Acquirer bank is facing technical difficulties, please try again later.",
        52 => "Invalid OLP.",
        53 => "Merchant is not found in OLP Engine DB.",
        54 => "SADAD is facing technical difficulties, please try again later.",
        55 => "OLP ID Alias is not valid. Please contact your bank.",
        56 => "OLP ID Alias does not exist. Please enter a valid OLP ID Alias.",
        57 => "Transaction amount exceeds the daily transaction limit.",
        58 => "Transaction amount exceeds the allowed limit per transaction.",
        59 => "Merchant Name and SADAD Merchant ID do not match.",
        60 => "The entered OLP password is incorrect. Please provide a valid password.",
        61 => "Failed to create Token.",
        62 => "Token has been created.",
        63 => "Token has been updated.",
        64 => "3-D Secure check requested.",
        65 => "Transaction waiting for customer’s action.",
        66 => "Merchant reference already exists.",
        67 => "Dynamic descriptor not configured for selected payment.",
        68 => "SDK service is inactive.",
        70 => "device_id mismatch.",
        71 => "Failed to initiate connection.",
        72 => "Transaction has been cancelled by the consumer.",
        73 => "Invalid request format.",
        74 => "Transaction failed.",
        75 => "Transaction failed.",
        76 => "Transaction not found in OLP.",
        77 => "Error transaction code not found.",
        78 => "Failed to check fraud screening.",
        79 => "Transaction challenged by fraud.",
        83 => "Unexpected user behavior.",
        84 => "Transaction amount is either bigger than maximum or less than minimum amount accepted for the selected plan.",
        85 => "Selected installment plan does not exist.",
        86 => "Installment plan is not configured for Merchant account.",
        87 => "Card BIN does not match accepted issuer bank.",
        88 => "Token name was not created for this transaction.",
        89 => "Failed to retrieve digital wallet details.",
        90 => "Failed to perform operation transaction in review.",
        93 => "service inactive.",
        99 => "Failed to execute service.",
        662 => "Operation not allowed. The specified order is not confirmed yet.",
        666 => "Transaction declined.",
        773 => "Transaction closed.",
        777 => "The transaction has been processed, but failed to receive confirmation.",
        778 => "Session timed-out.",
        779 => "Transformation error.",
        780 => "Transaction number transformation error.",
        781 => "Message or response code transformation error.",
        783 => "Installments service inactive.",
        785 => "Transaction blocked by fraud checker.",
        787 => "Failed to authenticate the user.",
    ];

    protected static $successCodes = [0, 62, 63];
    protected static $pendingCodes = [64, 65];

    /**
     * The response code is made up of two parts: status (2 digits) and result (3 digits). We need the digits.
     * @param $code
     * @return int
     */
    public static function getStatusPart($code)
    {
        if (strlen((string)$code) == 5) {
            $code = substr((string)$code, 2);
        }
        return (int)$code;
    }

    /**
     * @param int $code
     * @return bool
     */
    public static function isSuccess($code)
    {
        return array_search(static::getStatusPart($code), static::$successCodes) !== false;
    }

    /**
     * @param int $code
     * @return bool
     */
    public static function isPending($code)
    {
        return array_search(static::getStatusPart($code), static::$pendingCodes) !== false;
    }

    /**
     * @param int $code
     * @return bool
     */
    public static function isFailure($code)
    {
        return !static::isSuccess($code) && !static::isPending($code);
    }

    /**
     * @param int $code
     * @return string|null
     */
    public static function message($code)
    {
        return array_key_exists(static::getStatusPart($code), static::$codes) ? static::$codes[static::getStatusPart($code)] : null;
    }

}