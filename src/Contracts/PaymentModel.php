<?php

namespace ItvisionSy\Payment\PayFort\Contracts;

interface PaymentModel
{

    public function reference();

    public function amount();

    public function currency();

    public function description();

    public function customerEmail();

    public function customerName();

    public function payfortId();

    public function authorizationCode();

}