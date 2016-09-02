<?php

/*
 * Example code for Authorizing & Capture
 */

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Payeezy_Client();
$client->setApiKey("y2sdDUJ0gGnxTyQFUGqDrutPHQ7LbeCS");
$client->setApiSecret("3472aaf343dce18899ed72af1c9f98f5c8b0912c0bfe3defe97cf2d158c0f96f");
$client->setMerchantToken("fdoa-0e8a514fecf2a834e0613becce6e98b30e8a514fecf2a834");
$client->setUrl("https://api-qa.payeezy.com/v1/transactions");

$authorize_card_transaction = new Payeezy_Token($client);

$authorize_response = $authorize_card_transaction->authorize(
    [
        "merchant_ref" => "Astonishing-Sale",
        "transaction_type" => "authorize",
        "method" => "token",
        "amount" => "200",
        "currency_code" => "USD",
        "token" => array(
          "token_type" => "FDToken",
          "token_data" => array(
            "type" => "visa",
            "value" => "2537446225198291",
            "cardholder_name" => "JohnSmith",
            "exp_date" => "1030"
          )
        )
    ]
);
echo "<pre>";
var_dump($authorize_response);
echo "</pre>";
