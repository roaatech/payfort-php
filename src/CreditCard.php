<?php

namespace ItvisionSy\Payment\PayFort;

/*
 * Acknowledgement: this class is based on the following class:
 * Class: CreditCard Class (http://www.phpclasses.org/package/441-PHP-Validate-credit-cards-and-detect-the-type-of-card.html)
 * Author: Daniel Froz Costa (http://www.phpclasses.org/browse/author/41459.html)
 *
 * Documentation:
 *
 * Card Type                   Prefix           Length     Check digit
 * - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
 * MasterCard                  51-55            16         mod 10
 * Visa                        4                13, 16     mod 10
 * AMEX                        34, 37           15         mod 10
 * Dinners Club/Carte Blanche  300-305, 36, 38  14         mod 10
 * Discover                    6011             16         mod 10
 * enRoute                     2014, 2149       15         any
 * JCB                         3                16         mod 10
 * JCB                         2131, 1800       15         mod 10
 *
 * More references:
 * http://www.beachnet.com/~hstiles/cardtype.hthml
 *
 * $Id: creditcard_class.php,v 1.1 2002/02/16 16:02:12 daniel Exp $
 *
 */

class CreditCard {

    const TYPE_UNKNOWN = 0;
    const TYPE_MASTERCARD = 1;
    const TYPE_VISA = 2;
    const TYPE_AMEX = 3;
    const TYPE_DINNERS = 4;
    const TYPE_DISCOVER = 5;
    const TYPE_ENROUTE = 6;
    const TYPE_JCB = 7;
    const ERROR_OK = 0;
    const ERROR_ECALL = 1;
    const ERROR_EARG = 2;
    const ERROR_ETYPE = 3;
    const ERROR_ENUMBER = 4;
    const ERROR_EFORMAT = 5;
    const ERROR_ECANTYPE = 6;
    const ERROR_EXPIRY_INVALID = 7;

    protected $cardNumber;
    protected $cardHolderName;
    protected $cardExpiryDate;
    protected $cardCVV2;
    protected $type;
    protected $cardNumberErrorNumber;
    protected $cardExpiryErrorNumber;

    /**
     * CreditCard constructor.
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @param string $cardCVV2
     */
    public function __construct($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2) {
        $this->set($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2);
    }

    /**
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @param string $cardCVV2
     * @return $this;
     */
    public function set($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2) {
        $this->setCardNumber(preg_replace("#[^0-9]#", "", (string) $cardNumber));
        $this->setCardHolderName($cardHolderName);
        $this->setCardCVV2($cardCVV2);
        $this->setCardExpiryDate($cardExpiryDate);
        return $this;
    }

    /**
     * @param string|number $cardNumber
     * @param string $cardHolderName
     * @param string $cardExpiryDate in one of the formats: YY/MM, YY-MM, YYMM, YYYYMM, YYYY/MM, YYYY-MM
     * @param string $cardCVV2
     * @return CreditCard|static|$this
     */
    public static function make($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2) {
        return new static($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2);
    }

    /**
     * @param array $postData
     * @return CreditCard|null
     */
    public static function makeFromPostData(array $postData) {
        $cardNumber = $cardHolderName = $cardExpiryDate = $cardCVV2 = $cardExpiryMonth = $cardExpiryYear = null;
        foreach ($postData as $key => $value) {
            if (preg_match("#card_number|number#", $key)) {
                $cardNumber = $value;
            } elseif (preg_match("#holder|name|owner#", $key)) {
                $cardHolderName = $value;
            } elseif (preg_match("#expiry#", $key)) {
                if (preg_match("#month#", $key)) {
                    $cardExpiryMonth = $value;
                } elseif (preg_match("#year#", $key)) {
                    $cardExpiryYear = $value;
                } else {
                    $cardExpiryDate = $value;
                }
            } elseif (preg_match("#security|cvv2|cvv#", $key)) {
                $cardCVV2 = $value;
            }
        }
        if (!$cardExpiryDate && $cardExpiryMonth && $cardExpiryYear) {
            $cardExpiryDate = $cardExpiryYear . $cardExpiryMonth;
        }
        if (!$cardExpiryDate || !$cardHolderName || !$cardNumber || !$cardCVV2) {
            return null;
        }
        return static::make($cardNumber, $cardHolderName, $cardExpiryDate, $cardCVV2);
    }

    /**
     * @return null|string
     */
    public function getCardType() {
        return $this->detectTypeString();
    }

    /**
     * @return null|string
     */
    protected function detectTypeString() {
        if (!$this->cardNumber) {
            if (!$this->type) {
                $this->cardNumberErrorNumber = static::ERROR_EARG;
            }
        } else {
            $this->type = $this->detectType($this->cardNumber);
        }

        if (!$this->type) {
            $this->cardNumberErrorNumber = static::ERROR_ETYPE;
            return NULL;
        }

        switch ($this->type) {
            case static::TYPE_MASTERCARD:
                return "MASTERCARD";
            case static::TYPE_VISA:
                return "VISA";
            case static::TYPE_AMEX:
                return "AMEX";
            case static::TYPE_DINNERS:
                return "DINNERS";
            case static::TYPE_DISCOVER:
                return "DISCOVER";
            case static::TYPE_ENROUTE:
                return "ENROUTE";
            case static::TYPE_JCB:
                return "JCB";
            default:
                $this->cardNumberErrorNumber = static::ERROR_ECANTYPE;
                return NULL;
        }
    }

    /**
     * @return int
     */
    protected function detectType() {
        if (!$this->cardNumber) {
            $this->cardNumberErrorNumber = static::ERROR_ECALL;
            return static::TYPE_UNKNOWN;
        }

        if (preg_match("/^5[1-5]\d{14}$/", $this->cardNumber)) {
            $this->type = static::TYPE_MASTERCARD;
        } elseif (preg_match("/^4(\d{12}|\d{15})$/", $this->cardNumber)) {
            $this->type = static::TYPE_VISA;
        } elseif (preg_match("/^3[47]\d{13}$/", $this->cardNumber)) {
            $this->type = static::TYPE_AMEX;
        } else if (preg_match("/^[300-305]\d{11}$/", $this->cardNumber) || preg_match("/^3[68]\d{12}$/", $this->cardNumber)) {
            $this->type = static::TYPE_DINNERS;
        } elseif (preg_match("/^6011\d{12}$/", $this->cardNumber)) {
            $this->type = static::TYPE_DISCOVER;
        } elseif (preg_match("/^2(014|149)\d{11}$/", $this->cardNumber)) {
            $this->type = static::TYPE_ENROUTE;
        } elseif (preg_match("/^3\d{15}$/", $this->cardNumber) || preg_match("/^(2131|1800)\d{11}$/", $this->cardNumber)) {
            $this->type = static::TYPE_JCB;
        }

        if (!$this->type) {
            $this->cardNumberErrorNumber = static::ERROR_ECANTYPE;
            return static::TYPE_UNKNOWN;
        }
        return $this->type;
    }

    /**
     * Returns the type of the credit card number.
     *
     * Note that it does not validate the credit card number. It only detects the type based on the first identifier digits.
     *
     * @param string $cardNumber
     * @param boolean $returnNames
     * @return string
     */
    public static function detectCreditCardtype($cardNumber, $returnNames = true) {

        if (preg_match("/^5[1-5]/", $cardNumber)) {
            $type = static::TYPE_MASTERCARD;
        } elseif (preg_match("/^4/", $cardNumber)) {
            $type = static::TYPE_VISA;
        } elseif (preg_match("/^3[47]/", $cardNumber)) {
            $type = static::TYPE_AMEX;
        } else if (preg_match("/^[300-305]/", $cardNumber) || preg_match("/^3[68]/", $cardNumber)) {
            $type = static::TYPE_DINNERS;
        } elseif (preg_match("/^6011/", $cardNumber)) {
            $type = static::TYPE_DISCOVER;
        } elseif (preg_match("/^2(014|149)/", $cardNumber)) {
            $type = static::TYPE_ENROUTE;
        } elseif (preg_match("/^3/", $cardNumber) || preg_match("/^(2131|1800)/", $cardNumber)) {
            $type = static::TYPE_JCB;
        } else {
            $type=static::TYPE_UNKNOWN;
        }

        if ($returnNames) {
            switch ($type) {
                case static::TYPE_MASTERCARD:
                    $type="MASTERCARD";
                    break;
                case static::TYPE_VISA:
                    $type="VISA";
                    break;
                case static::TYPE_AMEX:
                    $type="AMEX";
                    break;
                case static::TYPE_DINNERS:
                    $type="DINNERS";
                    break;
                case static::TYPE_DISCOVER:
                    $type="DISCOVER";
                    break;
                case static::TYPE_ENROUTE:
                    $type="ENROUTE";
                    break;
                case static::TYPE_JCB:
                    $type="JCB";
                    break;
                default:
                    $type="UNKNOWN";
                    break;
            }
        }

        return $type;
    }

    /**
     * @return int
     */
    public function errno() {
        return $this->cardNumberErrorNumber ?: $this->cardExpiryErrorNumber;
    }

    /**
     * @return string
     */
    public function error() {
        switch ($this->cardNumberErrorNumber) {
            case static::ERROR_ECALL:
                return "Invalid call for this method";
            case static::ERROR_ETYPE:
                return "Invalid card type";
            case static::ERROR_ENUMBER:
                return "Invalid card number";
            case static::ERROR_EFORMAT:
                return "Invalid format";
            case static::ERROR_ECANTYPE:
                return "Cannot detect the type of your card";
            case static::ERROR_OK:
            default:
        }
        switch ($this->cardExpiryErrorNumber) {
            case static::ERROR_EXPIRY_INVALID:
                return "Invalid expiry date";
        }
        return "Success";
    }

    /**
     * @param string $numberKey
     * @param string $holderKey
     * @param string $expiryKey
     * @param string $securityKey
     * @return array
     */
    public function toArray($numberKey = 'card_number', $holderKey = 'card_holder_name', $expiryKey = 'expiry_date', $securityKey = 'card_security_code') {
        $data = [];
        $data[$numberKey] = $this->getCardNumber();
        $data[$expiryKey] = $this->getCardExpiryDate();
        $data[$securityKey] = $this->getCardCVV2();
        $data[$holderKey] = $this->getCardHolderName();
        return $data;
    }

    /**
     * @return bool|string
     */
    public function getCardNumber() {
        if (!$this->cardNumber) {
            $this->cardNumberErrorNumber = static::ERROR_ECALL;
            return false;
        }

        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     * @return CreditCard
     */
    public function setCardNumber($cardNumber) {

        $this->cardNumber = (string) $cardNumber;
        $this->type = static::TYPE_UNKNOWN;
        $this->cardNumberErrorNumber = static::ERROR_OK;

        $this->check();
        return $this;
    }

    /**
     * @return string YYMM
     */
    public function getCardExpiryDate() {
        return $this->cardExpiryDate;
    }

    /**
     * @param string $cardExpiryDate
     * @return CreditCard
     */
    public function setCardExpiryDate($cardExpiryDate) {
        $raw = preg_replace('#[^0-9]#', '', $cardExpiryDate);
        $len = strlen($raw);
        switch ($len) {
            case 4:
                $year = substr($raw, 0, 2);
                $month = substr($raw, 2, 2);
                break;
            case 6:
                $year = substr($raw, 2, 2);
                $month = substr($raw, 4, 2);
                break;
            default:
                $year = null;
                $month = null;
        }
        if (!$year || !$month) {
            $this->cardExpiryErrorNumber = static::ERROR_EXPIRY_INVALID;
            $this->cardExpiryDate = null;
        } else {
            $this->cardExpiryErrorNumber = static::ERROR_OK;
            $this->cardExpiryDate = $year . $month;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCardCVV2() {
        return $this->cardCVV2;
    }

    /**
     * @param string $cardCVV2
     * @return CreditCard
     */
    public function setCardCVV2($cardCVV2) {
        $this->cardCVV2 = (string) $cardCVV2;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardHolderName() {
        return $this->cardHolderName;
    }

    /**
     * @param string $cardHolderName in one of the formats: YY/MM, YYMM, YYYYMM, YYYY/MM
     * @return CreditCard
     */
    public function setCardHolderName($cardHolderName) {
        $this->cardHolderName = (string) $cardHolderName;
        return $this;
    }

    /**
     * @return bool
     */
    protected function check() {
        if (!$this->detectType($this->cardNumber)) {
            $this->cardNumberErrorNumber = static::ERROR_ETYPE;
            return false;
        }
        if ($this->mod10($this->cardNumber)) {
            $this->cardNumberErrorNumber = static::ERROR_ENUMBER;
            return false;
        }
        return true;
    }

    protected function mod10() {
        for ($sum = 0, $i = strlen($this->cardNumber) - 1; $i >= 0; $i--) {
            $sum += $this->cardNumber[$i];
            $doubdigit = "" . (2 * $this->cardNumber[--$i]);
            for ($j = strlen($doubdigit) - 1; $j >= 0; $j--) {
                $sum += $doubdigit[$j];
            }
        }
        return $sum % 10;
    }

}
