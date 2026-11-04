<?php
// UPI QR payment configuration (no payment gateway needed).
// Use your own UPI ID. Never use someone else's.

define('UPI_ID', 'demo_not_for_payment');          // change to your UPI ID
define('UPI_NAME', 'Diagnostic Center');   // name shown in the UPI app
define('UPI_CURRENCY', 'INR');

// Builds the UPI payment link that goes inside the QR code
function upi_payment_link(float $amount, string $note): string {
    return 'upi://pay?pa=' . urlencode(UPI_ID)
         . '&pn=' . urlencode(UPI_NAME)
         . '&am=' . number_format($amount, 2, '.', '')
         . '&cu=' . UPI_CURRENCY
         . '&tn=' . urlencode($note);
}

// Checks that a UTR (transaction reference) is exactly 12 digits
function is_valid_utr(string $utr): bool {
    return preg_match('/^[0-9]{12}$/', $utr) === 1;
}