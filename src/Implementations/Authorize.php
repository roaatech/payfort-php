<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 10:49 AM
 */

namespace ItvisionSy\Payment\PayFort\Implementations;

use ItvisionSy\Payment\PayFort\Contracts\TokenizableImplementation;

class Authorize extends \ItvisionSy\Payment\PayFort\Operations\Authorize implements TokenizableImplementation
{

    use \ItvisionSy\Payment\PayFort\Traits\TokenizableImplementation;

}