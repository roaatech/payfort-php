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
            'token_name' => generateTokenName($model),
            'merchant_identifier' => $this->config->getMerchantIdentifier(),
            'access_code' => $this->config->getAccessCode(),
            'merchant_reference' => $model->reference(),
            'service_command' => 'TOKENIZATION',
            'language' => $this->config->getLanguage(),
            'return_url' => $this->config->getResponseUrlTokenization(),
        ];
        $signature = Sign::make($this->config)->forRequest($this->signatureData($data));
        $data['signature'] = $signature;
        if ($this->config->isSendAsNormalHttpPost()) {
            $this->sendAsNormalPostRequest($data, $model, $card);
        } else {
            $this->sendAsInternalPostRequest($data, $model, $card);
        }
    }

    /**
     * @return string
     */
    protected function apiUrl() {
        return $this->config->isSandbox() ? "https://sbcheckout.PayFort.com/FortAPI/paymentPage" : "https://checkout.PayFort.com/FortAPI/paymentPage";
    }

    protected function sendAsNormalPostRequest(array $data, PaymentModel $model, CreditCard $card) {
        echo <<<FORM
<html><head></head><body> <form method="POST" action="{$this->apiUrl()}" id="authorize-frm"> <input type="hidden" name="card_number" value="{$card->getCardNumber()}"/> <input type="hidden" name="expiry_date" value="{$card->getCardExpiryDate()}"/> <input type="hidden" name="card_holder_name" value="{$card->getCardHolderName()}"/> <input type="hidden" name="card_security_code" value="{$card->getCardCVV2()}"/> <input type="hidden" name="service_command" value="{$data["service_command"]}"/> <input type="hidden" name="merchant_identifier" value="{$data["merchant_identifier"]}"/> <input type="hidden" name="access_code" value="{$data["access_code"]}"/> <input type="hidden" name="signature" value="{$data["signature"]}"/> <input type="hidden" name="merchant_reference" value="{$data["merchant_reference"]}"/> <input type="hidden" name="language" value="{$data["language"]}"/> <input type="hidden" name="return_url" value="{$data["return_url"]}"/> </form><script>function submitForm(){document.getElementById("authorize-frm").submit();}submitForm();</script></body></html>
FORM;
        exit;
    }

    protected function sendAsInternalPostRequest(array $data, PaymentModel $model, CreditCard $card) {
        return $this->sendAsInternalPostRequest($data, $model, $card);
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
