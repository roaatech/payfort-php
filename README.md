# payfort-php-integration
Payfort payment gateway integration library for PHP
=======
# 1 Setup
## 1.1 Automatically using composer
Just require it `composer require itvisionsy/payfort-php`.

## 1.2 Manually using Composer
You will need to add the `ItvisionSy\Payment\PayFort\` as a PSR-4 namespace pointing to the path of the library root, and the `autoload.php` file at the files section.
Let us say you have the following structure:
```
-/              <= Project root
  - Libraries/
    - PayFort/  <= This is the payment library
  composer.json <= The composer.json config file
```
In the `composer.json` file, you need to have the following definition:
```json
  "autoload": {
    "psr-4": {
      "ItvisionSy\\Payment\\PayFort\\": "Libraries/PayFort/src/"
    },
    "files": [
      "Libraries/PayFort/autoload.php"
    ]
  }
```
## 1.3 Manually using Autoload.php
You will need to require the `autoload.php` file before using the library.
# 2 Configuration
You can configure the payment in one of following ways:
 1. Create a file called `payfort_config.php` in the server root.
    This file should return an array of configuration.
    Following is a sample of the file content.
    ```php
    <?php return [
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
    ```
 1. Before using any function or method of the payment, use the public static method `ItvisionSy\Payment\PayFort\Config::setDefaultConfig()` and pass an array of the config values.
    Following is a sample of the usage of this method:
    ```php
    <?php
    \ItvisionSy\Payment\PayFort\Config::setDefaultConfig([
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
    ]);
    ```
 1. You can manually initiate a config object and store it in the `$GLOBALS` array as `payfort_config`.
    Following is a sample of this method:
    ```php
    <?php
    $GLOBALS['payfort_config'] = \ItvisionSy\Payment\PayFort\Config::make()
        ->setSandbox(true)
        ->setMerchantIdentifier('payfort_merchant_identifier_string')
        ->setAccessCode('payfort_merchant_access_code_string')
        ->setLanguage(\ItvisionSy\Payment\PayFort\Config::LANG_AR)
        ->setShaType(\ItvisionSy\Payment\PayFort\Config::SHA_TYPE_SHA256)
        ->setShaRequestPhrase('request_phrase_here')
        ->setShaResponsePhrase('response_phrase_here')
        ->setResponseUrlTokenization('http://your_redirect_back_url_to_receive_tokenization_result')
        ->setResponseUrlPurchase('http://your_redirect_back_url_to_receive_purchase_result')
        ->setResponseUrlAuthorization('http://your_redirect_back_url_to_receive_authorize_result')
        ->setSendAsNormalHttpPost(true)
        ->setModelLoader($your_model_loader_callable);
    ```
 1. You can define a public helper function called `payfort_config` which will always return an object of `\ItvisionSy\Payment\PayFort\Config`.
    This is an example:
    ```php
    <?php
    function payfort_config(){
        return \ItvisionSy\Payment\PayFort\Config::make()
            ->setSandbox(true)
            ->setMerchantIdentifier('payfort_merchant_identifier_string')
            ->setAccessCode('payfort_merchant_access_code_string')
            ->setLanguage(\ItvisionSy\Payment\PayFort\Config::LANG_AR)
            ->setShaType(\ItvisionSy\Payment\PayFort\Config::SHA_TYPE_SHA256)
            ->setShaRequestPhrase('request_phrase_here')
            ->setShaResponsePhrase('response_phrase_here')
            ->setResponseUrlTokenization('http://your_redirect_back_url_to_receive_tokenization_result')
            ->setResponseUrlPurchase('http://your_redirect_back_url_to_receive_purchase_result')
            ->setResponseUrlAuthorization('http://your_redirect_back_url_to_receive_authorize_result')
            ->setSendAsNormalHttpPost(true)
            ->setModelLoader($your_model_loader_callable);
    }
    ```
# 3 Usage

## 3.1 Understand the logic flow
PayFort has two main types of operations: token-based and id-based.
 * Token-based operations are `AUTHORIZE` and `PURCHASE`, which are the ones used to charge credit cards. On success, they return a fort_id string, which is used in id-based operations.
 * id-based operations are mainly any operation to alter, finish, enquire, or cancel an token-based operation. They are `CAPTURE` a full or part amount of `AUTHORIZE` operation, or `VOID` it. `REFUND` any captured amount. And other maintenance operations.

Token based operations need a token to be generated for specific credit card info. To do this, a
full HTTP POST request to be send to PayFort contains the credit card information
along side the merchant identification. If succeeded, it will return a token string
for this credit card. This token can be used again in later operations for the
same user. After that, all the remaining operations are normal HTTP API-call operations,
unless there is 3rd party security check, which will require a redirect cycle.
Both `PURCHASE` and `AUTHORIZE` operations require a token to be used.
If you already have a token generated for a user's credit card, you can bypass the
tokenization cycle and jump directly to purchase process.


## 3.2 How to use

There are two approaches:
 1. Extend the required operation's abstract class and implement the succeeded/failed functions.
 1. Call the required operation's implementation and pass the succeeded/failed callables.

# LICENSE
All issued under (MIT license)[LICENSE]

# Thanks
JetBrains for the all-product license for open source projects.
