<?php
require '../config/app.php';
require '../config/upi.php';
require_role('patient');
$title = 'Pay via UPI | DiagnosCare';
$basePath = '../';
$uid = user()['id'];
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT a.id, p.amount, p.status AS pay_status, t.name AS test_name
    FROM appointments a
    JOIN payments p ON p.appointment_id = a.id
    JOIN diagnostic_tests t ON t.id = a.test_id
    WHERE a.id = ? AND a.patient_id = ?");
$st->execute([$id, $uid]);
$appt = $st->fetch();
if (!$appt) { flash('danger', 'Appointment not found.'); redirect('dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $utr = trim($_POST['utr'] ?? '');
    if (!is_valid_utr($utr)) {
        flash('danger', 'UTR must be exactly 12 digits.');
    } else {
        $st = $pdo->prepare("UPDATE payments SET method='UPI', transaction_id=?, status='Awaiting Verification'
                             WHERE appointment_id=? AND status='Pending'");
        $st->execute([$utr, $id]);
        flash('success', 'Payment submitted. It will be verified shortly.');
        redirect("payment.php?id=$id");
    }
}

$link = upi_payment_link((float)$appt['amount'], 'Appointment #' . $id);
require '../partials/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card p-4 text-center">
    <h3>Pay via UPI</h3>
    <p class="text-muted"><?= e($appt['test_name']) ?> | Amount: <strong><?= money($appt['amount']) ?></strong></p>

    <?php if ($appt['pay_status'] === 'Pending'): ?>
      <div id="qrcode" class="d-inline-block mx-auto mb-2"></div>
      <p>Scan with GPay, PhonePe, Paytm or any UPI app</p>
      <hr>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label class="form-label fw-semibold">Enter your 12-digit UTR number after paying</label>
        <input type="text" name="utr" maxlength="12" pattern="[0-9]{12}" class="form-control mb-3" required>
        <button class="btn btn-primary">I have paid</button>
      </form>
    <?php else: ?>
      <p class="fs-5">Payment status: <strong><?= e($appt['pay_status']) ?></strong></p>
      <a href="dashboard.php" class="btn btn-primary">Back to dashboard</a>
    <?php endif; ?>
  </div>
</div></div>

<?php if ($appt['pay_status'] === 'Pending'): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById("qrcode"), { text: <?= json_encode($link) ?>, width: 220, height: 220 });
</script>
<?php endif; ?>
<?php require '../partials/footer.php'; ?>