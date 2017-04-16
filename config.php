<?php

return [
    'sandbox' => true,
    'merchant_identifier' => 'payfort_merchant_identifier_string',
    'access_code' => 'payfort_merchant_access_code_here',
    'language' => \ItvisionSy\Payment\PayFort\Config::LANG_EN,
    'sha_type' => \ItvisionSy\Payment\PayFort\Config::SHA_TYPE_SHA256,
    'sha_request_phrase' => 'request_phrase_here',
    'sha_response_phrase' => 'response_phrase_here',
    'response_url_tokenization' => 'http://your_redirect_back_url_to_receive_tokenization_result',
    'response_url_purchase' => 'http://your_redirect_back_url_to_receive_purchase_result',
    'response_url_authorization' => 'http://your_redirect_back_url_to_receive_authorize_result',
    'send_as_normal_http_post' => true,
    'model_loader' => $your_model_loader_callable
];
