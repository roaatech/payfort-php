<?php

namespace ItvisionSy\Payment\PayFort\Contracts;


interface SessionHandler
{

    public function set($key, $value);

    public function get($key, $default = null);

    public function clear($key);

}