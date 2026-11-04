<?php
define('UPI_ID', 'demo_not_for_payment');         // your own UPI ID
define('UPI_NAME', 'DiagnosCare');

function upi_payment_link(float $amount, string $note): string {
    return 'upi://pay?pa=' . urlencode(UPI_ID)
         . '&pn=' . urlencode(UPI_NAME)
         . '&am=' . number_format($amount, 2, '.', '')
         . '&cu=INR&tn=' . urlencode($note);
}

function is_valid_utr(string $utr): bool {
    return preg_match('/^[0-9]{12}$/', $utr) === 1;
}