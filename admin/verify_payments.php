<?php
require '../config/app.php';
require_role('admin');
$title = 'Verify Payments | DiagnosCare';
$basePath = '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pid = (int)($_POST['payment_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $st = $pdo->prepare("UPDATE payments SET status='Paid'
                             WHERE id=? AND status='Awaiting Verification'");
        $st->execute([$pid]);
        flash('success', 'Payment approved.');
    } elseif ($action === 'reject') {
        // Back to Pending so the patient can scan and submit a correct UTR again
        $st = $pdo->prepare("UPDATE payments SET status='Pending'
                             WHERE id=? AND status='Awaiting Verification'");
        $st->execute([$pid]);
        flash('warning', 'Payment rejected. The patient can submit it again.');
    }
    redirect('verify_payments.php');
}

$rows = $pdo->query("SELECT p.id, p.amount, p.transaction_id,
        a.id AS appt_id, a.appointment_date, a.appointment_time, t.name AS test_name
    FROM payments p
    JOIN appointments a ON a.id = p.appointment_id
    JOIN diagnostic_tests t ON t.id = a.test_id
    WHERE p.status = 'Awaiting Verification'
    ORDER BY p.id")->fetchAll();

require '../partials/header.php';
?>
<h2 class="dashboard-title mb-4">Verify UPI Payments</h2>
<div class="card p-3">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr><th>Appt #</th><th>Test</th><th>Date</th><th>Amount</th><th>UTR</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= $r['appt_id'] ?></td>
          <td><?= e($r['test_name']) ?></td>
          <td><?= e($r['appointment_date']) ?> <?= date('h:i A', strtotime($r['appointment_time'])) ?></td>
          <td><?= money($r['amount']) ?></td>
          <td><?= e($r['transaction_id']) ?></td>
          <td>
            <form method="post" class="d-flex gap-2 m-0">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="payment_id" value="<?= $r['id'] ?>">
              <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="6" class="text-center text-muted">No payments waiting for verification.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require '../partials/footer.php'; ?>