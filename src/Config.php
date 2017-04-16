<?php

namespace ItvisionSy\Payment\PayFort;

use Exception;
use ItvisionSy\Payment\PayFort\Contracts\PaymentModel;

/**
 * Class Config
 * @package ItvisionSy\Payment\PayFort
 */
class Config
{

    CONST SHA_TYPE_SHA128 = 'sha1';
    CONST SHA_TYPE_SHA256 = 'sha256';
    CONST SHA_TYPE_SHA512 = 'sha512';

    const LANG_EN = 'en';
    const LANG_AR = 'ar';

    /** @var array */
    protected static $_defaultConfig = [
        'sandbox' => false,
        'merchant_identifier' => null,
        'access_code' => null,
        'language' => 'en',
        'sha_type' => 'sha256',
        'sha_request_phrase' => null,
        'sha_response_phrase' => null,
        'response_url_tokenization' => null,
        'response_url_purchase' => null,
        'response_url_authorization' => null,
        'send_as_normal_http_post' => true,
        //'model_loader' will get injected at call time
    ];
    /** @var bool */
    protected $sandbox = false;
    /** @var  string */
    protected $merchantIdentifier;
    /** @var  string */
    protected $accessCode;
    /** @var  string */
    protected $language = 'en';
    /** @var  string */
    protected $shaType;
    /** @var  string */
    protected $shaRequestPhrase;
    /** @var  string */
    protected $shaResponsePhrase;
    /** @var  string */
    protected $responseUrlTokenization;
    /** @var  string */
    protected $responseUrlPurchase;
    /** @var  string */
    protected $responseUrlAuthorization;
    /** @var bool */
    protected $sendAsNormalHttpPost = true;
    /** @var callable static class wide model loader */
    protected static $SModelLoader;
    /** @var callable instance level model loader */
    protected $modelLoader;

    /**
     * Config constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = static::config($config);
        $this->setSandbox($config['sandbox']);
        $this->setMerchantIdentifier($config['merchant_identifier']);
        $this->setAccessCode($config['access_code']);
        $this->setLanguage($config['language']);
        $this->setShaType($config['sha_type']);
        $this->setShaRequestPhrase($config['sha_request_phrase']);
        $this->setShaResponsePhrase($config['sha_response_phrase']);
        $this->setResponseUrlTokenization($config['response_url_tokenization']);
        $this->setResponseUrlPurchase($config['response_url_purchase']);
        $this->setResponseUrlAuthorization($config['response_url_authorization']);
        $this->setSendAsNormalHttpPost(!!$config['send_as_normal_http_post']);
        $this->setModelLoader($config['model_loader']);
    }

    /**
     * @param array $config
     * @return array
     */
    public static function config(array $config = [])
    {
        return $config + static::defaultConfig();
    }

    /**
     * @return array
     */
    public static function defaultConfig()
    {
        return static::$_defaultConfig + ['model_loader' => static::$SModelLoader];
    }

    /**
     * @param array $config
     * @return array
     */
    public static function setDefaultConfig(array $config)
    {
        $config = static::config($config);
        static::$_defaultConfig = $config;
        return $config;
    }

    /**
     * @param array $config
     * @return static|$this|Config
     */
    public static function make(array $config = [])
    {
        return new static($config);
    }

    /**
     * @return boolean
     */
    public function isSandbox()
    {
        return !!$this->sandbox;
    }

    /**
     * @param boolean $sandbox
     * @return Config
     */
    public function setSandbox($sandbox)
    {
        $this->sandbox = !!$sandbox;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantIdentifier()
    {
        return $this->merchantIdentifier;
    }

    /**
     * @param string $merchantIdentifier
     * @return Config
     */
    public function setMerchantIdentifier($merchantIdentifier)
    {
        $this->merchantIdentifier = $merchantIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessCode()
    {
        return $this->accessCode;
    }

    /**
     * @param string $accessCode
     * @return Config
     */
    public function setAccessCode($accessCode)
    {
        $this->accessCode = $accessCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return Config
     * @throws Exception
     */
    public function setLanguage($language)
    {
        switch ($language) {
            case static::LANG_EN:
            case static::LANG_AR:
                $this->language = $language;
                break;
            default:
                throw new Exception("\$language should be either en OR ar");
        }
        return $this;
    }

    /**
     * @return bool|string
     */
    public function error()
    {
        if (!$this->getMerchantIdentifier()) {
            return "Merchant identifier is required";
        }
        if (!$this->getAccessCode()) {
            return "Access code is required";
        }
        return false;
    }

    /**
     * @return string
     */
    public function getShaRequestPhrase()
    {
        return $this->shaRequestPhrase;
    }

    /**
     * @param string $shaRequestPhrase
     * @return Config
     */
    public function setShaRequestPhrase($shaRequestPhrase)
    {
        $this->shaRequestPhrase = $shaRequestPhrase;
        return $this;
    }

    /**
     * @return string
     */
    public function getShaResponsePhrase()
    {
        return $this->shaResponsePhrase;
    }

    /**
     * @param string $shaResponsePhrase
     * @return Config
     */
    public function setShaResponsePhrase($shaResponsePhrase)
    {
        $this->shaResponsePhrase = $shaResponsePhrase;
        return $this;
    }

    /**
     * @return string
     */
    public function getShaType()
    {
        return $this->shaType;
    }

    /**
     * @param string $shaType
     * @return Config
     * @throws Exception
     */
    public function setShaType($shaType)
    {
        switch (strtolower($shaType)) {
            case static::SHA_TYPE_SHA128:
            case static::SHA_TYPE_SHA256:
            case static::SHA_TYPE_SHA512:
                break;
            default:
                throw new Exception(
                    "Invalid SHA type. Expect one of " . static::SHA_TYPE_SHA512 . ", "
                    . static::SHA_TYPE_SHA256 . ", or " . static::SHA_TYPE_SHA128 . ". Received {$shaType}");
        }
        $this->shaType = $shaType;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseUrlTokenization()
    {
        return $this->responseUrlTokenization;
    }

    /**
     * @param string $responseUrlTokenization
     * @return Config
     */
    public function setResponseUrlTokenization($responseUrlTokenization)
    {
        $this->responseUrlTokenization = $responseUrlTokenization;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSendAsNormalHttpPost()
    {
        return $this->sendAsNormalHttpPost;
    }

    /**
     * @param bool $sendAsNormalHttpPost
     * @return Config
     */
    public function setSendAsNormalHttpPost($sendAsNormalHttpPost)
    {
        $this->sendAsNormalHttpPost = $sendAsNormalHttpPost;
        return $this;
    }

    /**
     * @param $reference
     * @return PaymentModel
     */
    public function loadModelOrReturnFalse($reference)
    {
        $loader = $this->modelLoader ?: static::$SModelLoader;
        if (!$loader) {
            return false;
        }
        return $loader($reference);
    }

    /**
     * static model loader setter and getter
     * @param callable|null|false $modelLoader
     * @return bool|callable
     */
    public static function modelLoader($modelLoader = null)
    {
        if ($modelLoader && is_callable($modelLoader)) {
            static::$SModelLoader = $modelLoader;
            return true;
        } elseif ($modelLoader === false) {
            static::$SModelLoader = null;
            return false;
        } else {
            return static::$SModelLoader;
        }
    }

    /**
     * @return callable
     */
    public function getModelLoader()
    {
        return $this->modelLoader;
    }

    /**
     * @param callable $modelLoader
     * @return Config
     */
    public function setModelLoader($modelLoader)
    {
        $this->modelLoader = $modelLoader;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseUrlPurchase()
    {
        return $this->responseUrlPurchase;
    }

    /**
     * @param string $responseUrlPurchase
     * @return Config
     */
    public function setResponseUrlPurchase($responseUrlPurchase)
    {
        $this->responseUrlPurchase = $responseUrlPurchase;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseUrlAuthorization()
    {
        return $this->responseUrlAuthorization;
    }

    /**
     * @param string $responseUrlAuthorization
     * @return Config
     */
    public function setResponseUrlAuthorization($responseUrlAuthorization)
    {
        $this->responseUrlAuthorization = $responseUrlAuthorization;
        return $this;
    }

    /**
     * @return callable
     */
    public static function getSModelLoader()
    {
        return self::$SModelLoader;
    }

    /**
     * @param callable $SModelLoader
     */
    public static function setSModelLoader(callable $SModelLoader)
    {
        self::$SModelLoader = $SModelLoader;
    }

}