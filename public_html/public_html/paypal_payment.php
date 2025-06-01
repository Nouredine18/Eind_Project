<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

<<<<<<< Updated upstream
=======
include 'connect.php';
require 'vendor/autoload.php'; // Zorg ervoor dat je de nieuwe PayPal SDK hebt geïnstalleerd

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

>>>>>>> Stashed changes
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($amount <= 0 || $project_id <= 0) {
    die("Invalid amount or project ID.");
}

<<<<<<< Updated upstream
// Include PayPal SDK
require 'vendor/autoload.php';

use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;

// Set up PayPal API context
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'AUXm42rXnu72q2qaEpJ3BnHIXoY1_6rJ3l3BYXlNRorp6TfZXCW53js36gPCCYjbOEc_yDjBKhKSqYMK',     // ClientID
        'EPXABRvirlS_t8j6afINfcJCfVuy51rMj6FVbMTQDnoxp687TJtwu4xgQnxrUmmKr8yZehCbarHabOJH'  // ClientSecret
    )
);

// Create a new payer
$payer = new Payer();
$payer->setPaymentMethod('paypal');

// Set the amount
$amountObj = new Amount();
$amountObj->setTotal($amount);
$amountObj->setCurrency('USD');

// Create a transaction
$transaction = new Transaction();
$transaction->setAmount($amountObj);
$transaction->setDescription('Payment for project ID: ' . $project_id);

// Set redirect URLs
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("http://localhost/project/-CO2_Compensation/public_html/paypal_success.php?amount=$amount&project_id=$project_id")
    ->setCancelUrl("http://localhost/project/-CO2_Compensation/public_html/paypal_cancel.php");

// Create a payment
$payment = new Payment();
$payment->setIntent('sale')
    ->setPayer($payer)
    ->setTransactions([$transaction])
    ->setRedirectUrls($redirectUrls);

try {
    $payment->create($apiContext);
    header('Location: ' . $payment->getApprovalLink());
    exit();
} catch (Exception $ex) {
    die($ex);
=======
// Stel PayPal API-context in met de nieuwe PayPal Server SDK
$clientId = 'AUXm42rXnu72q2qaEpJ3BnHIXoY1_6rJ3l3BYXlNRorp6TfZXCW53js36gPCCYjbOEc_yDjBKhKSqYMK';
$clientSecret = 'EPXABRvirlS_t8j6afINfcJCfVuy51rMj6FVbMTQDnoxp687TJtwu4xgQnxrUmmKr8yZehCbarHabOJH';
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    "intent" => "CAPTURE",
    "purchase_units" => [[
        "amount" => [
            "value" => number_format($amount, 2, '.', ''), // Zorg ervoor dat het bedrag correct is geformatteerd
            "currency_code" => "EUR" // Veranderd van USD naar EUR
        ],
        "description" => "Donation for project ID: $project_id"
    ]],
    "application_context" => [
        "cancel_url" => "http://localhost/project/-CO2_Compensation/public_html/paypal_cancel.php", // Veranderd van stripe_cancel.php
        "return_url" => "http://localhost/project/-CO2_Compensation/public_html/paypal_success.php?amount=$amount&project_id=$project_id"
    ]
];

try {
    // Voer het verzoek uit en ontvang het antwoord
    $response = $client->execute($request);
    
    // Controleer het antwoord en stuur door naar de goedkeurings-URL
    foreach ($response->result->links as $link) {
        if ($link->rel == 'approve') {
            header('Location: ' . $link->href);
            exit();
        }
    }

    throw new Exception("No approval link found in PayPal response.");
} catch (Exception $ex) {
    echo "An error occurred: " . $ex->getMessage();
    // Log optioneel de fout of onderneem andere acties
>>>>>>> Stashed changes
}
?>
