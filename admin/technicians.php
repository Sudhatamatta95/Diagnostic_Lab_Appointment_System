<?php
require '../config/app.php';

if (!logged_in() || user()['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$rows = $pdo->query(
    "SELECT u.id, u.name, u.email, u.phone, t.picture
     FROM users u
     LEFT JOIN technicians t ON t.user_id = u.id
     WHERE u.role = 'technician'
     ORDER BY u.id DESC"
)->fetchAll();

$title = 'Technicians | DiagnosCare';
$basePath = '../';
require '../partials/header.php';
?>
<div class="container py-4">
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="fw-bold mb-0">Technicians</h3>
      <a href="add_technician.php" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add Technician</a>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr><th>Photo</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>
              <?php if (!empty($r['picture'])): ?>
                <img src="../<?= e($r['picture']) ?>" class="rounded-circle" style="width:44px;height:44px;object-fit:cover" alt="">
              <?php else: ?>
                <div class="avatar"><?= strtoupper(substr($r['name'], 0, 1)) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['email']) ?></td>
            <td><?= e($r['phone'] ?? '') ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="edit_technician.php?id=<?= (int)$r['id'] ?>">Edit</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="text-muted text-center">No technicians yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require '../partials/footer.php'; ?>