<?php
require 'database.php';
require 'payment.php';

$id  = (int)($_POST['appointment_id'] ?? 0);
$utr = trim($_POST['utr'] ?? '');

if (!is_valid_utr($utr)) {
    die("Invalid UTR. It must be exactly 12 digits.");
}

$stmt = $pdo->prepare(
    "UPDATE appointments
     SET payment_method = 'UPI QR',
         utr_number = ?,
         payment_status = 'Awaiting Verification'
     WHERE id = ?"
);
$stmt->execute([$utr, $id]);
?>
<!DOCTYPE html>
<html>
<body style="text-align:center; font-family:Arial;">
  <h2>Thank you!</h2>
  <p>Your payment details are submitted and will be verified shortly.</p>
  <p>Appointment ID: <?= $id ?> | UTR: <?= htmlspecialchars($utr) ?></p>
</body>
</html>