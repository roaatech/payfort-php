<?php

namespace ItvisionSy\Payment\PayFort;

class SessionHandler implements Contracts\SessionHandler
{
    protected $store;

    function __construct(array $store = null)
    {
        $this->store = $store ?: $_SESSION;
    }

    public function set($key, $value)
    {
        $this->store['key'] = $value;
    }

    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->store) ? $this->store[$key] : $default;
    }

    public function clear($key)
    {
        if (array_key_exists($key, $this->store)) {
            unset($this->store[$key]);
        }
    }
}