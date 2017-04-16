<?php

namespace ItvisionSy\Payment\PayFort;

class Sign
{

    /** @var Config|null */
    protected $config;

    public static function make(Config $config = null)
    {
        return new static($config);
    }

    /**
     * Sign constructor.
     * @param Config|null $config
     */
    public function __construct(Config $config = null)
    {
        $this->config = payfort_config($config);
    }

    public function forRequest(array $data)
    {
        return $this->sign($data, true);
    }

    public function forResponse(array $data)
    {
        return $this->sign($data, false);
    }

    /**
     * @param array $data
     * @param bool $signForRequest
     * @return mixed|string
     */
    protected function sign(array $data, $signForRequest = true)
    {
        $arrData = [] + $data;
        ksort($arrData);
        $phrase = !!$signForRequest ? $this->config->getShaRequestPhrase() : $this->config->getShaResponsePhrase();
        $shaString = $phrase;
        foreach ($arrData as $key => $value) {
            $shaString .= "$key=$value";
        }
        $shaString .= $phrase;
        $signature = hash($this->config->getShaType(), $shaString);
        return $signature;
    }

}