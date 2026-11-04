<?php
require 'db.php';
// IMPORTANT: add your existing admin login check here, for example:
// session_start();
// if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

if (isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    $status = ($_POST['action'] === 'approve') ? 'Paid' : 'Rejected';
    $stmt = $pdo->prepare("UPDATE appointments SET payment_status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

$stmt = $pdo->query(
    "SELECT id, amount, utr_number FROM appointments
     WHERE payment_status = 'Awaiting Verification'"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<body style="font-family:Arial;">
  <h2>Pending UPI Payments</h2>
  <table border="1" cellpadding="8">
    <tr><th>ID</th><th>Amount</th><th>UTR</th><th>Action</th></tr>
    <?php foreach ($rows as $r) { ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td>₹<?= htmlspecialchars($r['amount']) ?></td>
      <td><?= htmlspecialchars($r['utr_number']) ?></td>
      <td>
        <form method="POST" style="margin:0;">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button name="action" value="approve">Approve</button>
          <button name="action" value="reject">Reject</button>
        </form>
      </td>
    </tr>
    <?php } ?>
    <?php if (!$rows) { echo "<tr><td colspan='4'>No pending payments</td></tr>"; } ?>
  </table>
</body>
</html>