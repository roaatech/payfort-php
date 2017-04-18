<?php

namespace ItvisionSy\Payment\PayFort;

use ItvisionSy\Payment\PayFort\Contracts\PaymentModel;

class Tokenize {

    /** @var Config|null */
    protected $config;

    public static function make(Config $config = null) {
        return new static($config);
    }

    /**
     * Sign constructor.
     * @param Config|null $config
     */
    public function __construct(Config $config = null) {
        $this->config = payfort_config($config);
    }

    /**
     * Creates a TOKENIZATION request to proceed with a PURCHASE or AUTHORIZATION requests later
     *
     * This will generate an HTML form and auto submit it to PayFort URL.
     * @param PaymentModel $model
     * @param CreditCard $card
     */
    public function tokenize(PaymentModel $model, CreditCard $card) {
        $data = [
            'merchant_identifier' => $this->config->getMerchantIdentifier(),
            'access_code' => $this->config->getAccessCode(),
            'merchant_reference' => $model->reference(),
            'service_command' => 'TOKENIZATION',
            'language' => $this->config->getLanguage(),
            'return_url' => $this->config->getResponseUrlTokenization(),
        ];
        $signature = Sign::make($this->config)->forRequest($this->signatureData($data));
        $data['signature'] = $signature;
        $postData = $data + [
            'card_number' => $card->getCardNumber(),
            'expiry_date' => $card->getCardExpiryDate(),
            'card_holder_name' => $card->getCardHolderName(),
            'card_security_code' => $card->getCardCVV2()
        ];
        if ($this->config->isSendAsNormalHttpPost()) {
            $this->sendAsNormalPostRequest($postData);
        } else {
            $this->sendAsInternalPostRequest($postData);
        }
    }

    /**
     * @return string
     */
    protected function apiUrl() {
        return $this->config->isSandbox() ? "https://sbcheckout.PayFort.com/FortAPI/paymentPage" : "https://checkout.PayFort.com/FortAPI/paymentPage";
    }

    protected function sendAsNormalPostRequest(array $data) {
        echo "<html><head></head><body> <form method='POST' action='{$this->apiUrl()}' id='authorize-frm'>";
        foreach ($data as $key => $value) {
            echo "<input type='hidden' name='{$key}' value='{$value}' />";
        }
        echo "</form><script>function submitForm(){document.getElementById('authorize-frm').submit();}submitForm();</script></body></html>";
        exit;
    }

    protected function sendAsInternalPostRequest(array $data) {
        //@TODO:implement this
        return $this->sendAsNormalPostRequest($data);
    }

    protected function signatureData(array $data) {
        $signatureData = array_filter($data, function ($key) {
            return array_search($key, [
                        'token_name',
                        'merchant_identifier',
                        'access_code',
                        'merchant_reference',
                        'service_command',
                        'language',
                        'return_url'
                    ]) !== false;
        }, ARRAY_FILTER_USE_KEY);
        return $signatureData;
    }

}
