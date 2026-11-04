<?php
require '../config/app.php';

if (!logged_in() || user()['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$title = 'Add Technician | DiagnosCare';
$basePath = '../';
require '../partials/header.php';
?>
<div class="container py-4" style="max-width:600px">
  <div class="card p-4">
    <h3 class="fw-bold mb-3">Add Technician</h3>

    <form action="save_technician.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <label class="form-label">Name</label>
      <input class="form-control mb-3" type="text" name="name" required>

      <label class="form-label">Login Email</label>
      <input class="form-control mb-3" type="email" name="email" required>

      <label class="form-label">Mobile Number</label>
<input class="form-control mb-3" type="tel" name="phone" pattern="[6-9][0-9]{9}" maxlength="10" placeholder="10-digit mobile number" required>

      <label class="form-label">Temporary Password</label>
      <input class="form-control mb-3" type="text" name="password" minlength="6" required>

      <label class="form-label">Details</label>
      <textarea class="form-control mb-3" name="details" rows="4"></textarea>

      <label class="form-label">Picture (jpg/png)</label>
      <input class="form-control mb-3" type="file" name="picture" accept="image/*" required>

      <label class="form-label">Certificate (pdf/jpg/png)</label>
      <input class="form-control mb-3" type="file" name="certificate" accept=".pdf,image/*" required>

      <button class="btn btn-primary w-100">Add Technician</button>
    </form>
  </div>
</div>
<?php require '../partials/footer.php'; ?>