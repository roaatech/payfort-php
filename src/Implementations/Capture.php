<?php
/**
 * Created by PhpStorm.
 * User: mhh1422
 * Date: 03/01/2017
 * Time: 11:01 AM
 */

namespace ItvisionSy\Payment\PayFort\Implementations;

use ItvisionSy\Payment\PayFort\Contracts\NotTokenizableImplementation;

class Capture extends \ItvisionSy\Payment\PayFort\Operations\Capture implements NotTokenizableImplementation
{

    use \ItvisionSy\Payment\PayFort\Traits\NotTokenizableImplementation;

}