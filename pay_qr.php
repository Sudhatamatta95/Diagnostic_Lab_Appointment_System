<?php
require 'database.php';        // your PDO connection file
require 'payment.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$appt) die("Appointment not found");

$link = upi_payment_link((float)$appt['amount'], "Appointment #" . $id);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Pay via UPI QR</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body style="text-align:center; font-family:Arial;">
  <h2>Pay using UPI</h2>
  <p>Appointment #<?= $id ?> | Amount: <b>₹<?= htmlspecialchars($appt['amount']) ?></b></p>

  <div id="qrcode" style="display:inline-block;"></div>
  <p>Scan with GPay, PhonePe, Paytm or any UPI app</p>

  <hr>
  <h3>After paying, enter your 12-digit UTR number</h3>
  <form action="confirm_payment.php" method="POST">
    <input type="hidden" name="appointment_id" value="<?= $id ?>">
    <input type="text" name="utr" maxlength="12" pattern="[0-9]{12}"
           placeholder="12-digit UTR number" required>
    <button type="submit">I have paid</button>
  </form>

  <script>
    new QRCode(document.getElementById("qrcode"), {
      text: <?= json_encode($link) ?>,
      width: 220,
      height: 220
    });
  </script>
</body>
</html>