<?php

namespace ItvisionSy\Payment\PayFort;

use ItvisionSy\Payment\PayFort\Contracts\PaymentModel;
use ItvisionSy\Payment\PayFort\Contracts\OperationHandler;
use ItvisionSy\Payment\PayFort\Operations\Purchase as PaymentOperation;

class Payment
{

    /** @var PaymentModel */
    protected $model;
    /** @var CreditCard */
    protected $card;
    /** @var Config */
    protected $config;
    /** @var  OperationHandler */
    protected $handler;

    /**
     * Payment constructor.
     * Expects a
     * @param PaymentModel $model
     * @param CreditCard $creditCard
     * @param Config $config
     */
    function __construct(PaymentModel $model, CreditCard $creditCard, OperationHandler $handler, Config $config = null)
    {
        $this->model = $model;
        $this->card = $creditCard;
        $this->config = payfort_config($config);
        $this->handler = $handler;
    }

    public function authorize()
    {
        $payment = PaymentOperation::make($this->config)
            ->setCard($this->card)
            ->setMerchantReference($this->model->reference())
            ->setReturnUrl($this->handler->returnUrl())
            ->setRememberUser(false);
        echo $this->form($payment);
    }

    protected function form(PaymentOperation $payment)
    {
        echo <<<FORM
<form method="POST" action="{$payment->getConfig()->getUrl()}" id="authorize-frm">
    <input type="hidden" name="card_number" value="{$payment->getCard()->getCardNumber()}"/>
    <input type="hidden" name="expiry_date" value="{$payment->getCard()->getCardExpiryDate()}"/>
    <input type="hidden" name="card_security_code" value="{$payment->getCard()->getCardCVV2()}"/>
    <input type="hidden" name="service_command" value="{$payment->getServiceCommand()}"/>
    <input type="hidden" name="merchant_identifier" value="{$payment->getConfig()->getMerchantIdentifier()}"/>
    <input type="hidden" name="access_code" value="{$payment->getConfig()->getAccessCode()}"/>
    <input type="hidden" name="signature" value="{$payment->getSignature()}"/>
    <input type="hidden" name="merchant_reference" value="{$payment->getMerchantReference()}"/>
    <input type="hidden" name="language" value="{$payment->getConfig()->getLanguage()}"/>
    <input type="hidden" name="return_url" value="{$payment->getReturnUrl()}"/>
</form>
<script>
    document.getElementById("authorize-frm").submit();
</script>
FORM;
        exit;
    }

}