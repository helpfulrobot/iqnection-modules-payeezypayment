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

$authorize_card_transaction = new Payeezy_Transaction($client);

$authorize_response = $authorize_card_transaction->doPrimaryTransaction(
    [
    "merchant_ref" => "Astonishing-Sale",
    "transaction_type" => "authorize",
    "method" => "credit_card",
    "amount" => "1299",
    "currency_code" => "USD",
    "credit_card" => array(
    "type" => "visa",
    "cardholder_name" => "John Smith",
    "card_number" => "4788250000028291",
    "exp_date" => "1020",
    "cvv" => "123"
    )
    ]
);
echo "<pre>";
var_dump($authorize_response);
echo "</pre>";

$capture_card_transaction = new Payeezy_Transaction($client);
$capture_response = $capture_card_transaction->doSecondaryTransaction(
    $authorize_response->transaction_id,
    array(
        "transaction_type" => "capture",
        "method" => "credit_card",
        "amount"=> "1200",
        "transaction_tag" => $authorize_response->transaction_tag,
        "merchant_ref" => "Astonishing-Sale",
        "currency_code" => "USD",
        //"split_shipment" => "",
    )
);
echo "<pre>";
var_dump($capture_response);
echo "</pre>";
