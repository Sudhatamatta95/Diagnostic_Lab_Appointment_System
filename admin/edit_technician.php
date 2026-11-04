<?php
require '../config/app.php';

if (!logged_in() || user()['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$st = $pdo->prepare(
    "SELECT u.id, u.name, u.email, u.phone,
            t.id AS tech_id, t.details, t.picture, t.certificate
     FROM users u
     LEFT JOIN technicians t ON t.user_id = u.id
     WHERE u.id = ? AND u.role = 'technician' LIMIT 1"
);
$st->execute([$id]);
$tech = $st->fetch();

if (!$tech) {
    flash('danger', 'Technician not found.');
    header('Location: technicians.php');
    exit;
}

function saveUpload($field, $folder, $allowedExt, &$error) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;   // nothing chosen, keep the old file
    }
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $error = "Could not upload $field.";
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $error = "Invalid file type for $field.";
        return null;
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        $error = "$field is too large (max 5MB).";
        return null;
    }
    $newName = uniqid($field . '_', true) . '.' . $ext;
    $target  = __DIR__ . '/../assets/uploads/technicians/' . $folder . '/' . $newName;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        $error = "Could not save $field.";
        return null;
    }
    return 'assets/uploads/technicians/' . $folder . '/' . $newName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $error   = '';

    if ($name === '') {
        $error = 'Name is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $error = 'Enter a valid mobile number (10 digits).';
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
        $chk->execute([$email, $id]);
        if ($chk->fetch()) {
            $error = 'This email is already used by another account.';
        }
    }

    $newPic = $newCert = null;
    if ($error === '') {
        $newPic = saveUpload('picture', 'pictures', ['jpg', 'jpeg', 'png'], $error);
    }
    if ($error === '') {
        $newCert = saveUpload('certificate', 'certificates', ['pdf', 'jpg', 'jpeg', 'png'], $error);
    }

    if ($error === '') {
        $picture     = $newPic  ?? $tech['picture'];
        $certificate = $newCert ?? $tech['certificate'];

        try {
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?")
                ->execute([$name, $email, $phone, $id]);

            if ($tech['tech_id']) {
                $pdo->prepare(
                    "UPDATE technicians SET name = ?, phone = ?, details = ?, picture = ?, certificate = ? WHERE id = ?"
                )->execute([$name, $phone, $details, $picture, $certificate, $tech['tech_id']]);
            } else {
                // older technicians (like the demo one) have no profile row yet, so create it
                $pdo->prepare(
                    "INSERT INTO technicians (user_id, name, phone, details, picture, certificate) VALUES (?, ?, ?, ?, ?, ?)"
                )->execute([$id, $name, $phone, $details, $picture, $certificate]);
            }

            $pdo->commit();
            flash('success', 'Technician updated successfully.');
            header('Location: technicians.php');
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Database error: ' . $e->getMessage();
        }
    }

    flash('danger', $error);
    // keep what the admin typed
    $tech['name'] = $name;
    $tech['email'] = $email;
    $tech['phone'] = $phone;
    $tech['details'] = $details;
}

$title = 'Edit Technician | DiagnosCare';
$basePath = '../';
require '../partials/header.php';
?>
<div class="container py-4" style="max-width:600px">
  <div class="card p-4">
    <h3 class="fw-bold mb-3">Edit Technician</h3>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$tech['id'] ?>">

      <label class="form-label">Name</label>
      <input class="form-control mb-3" type="text" name="name" value="<?= e($tech['name']) ?>" required>

      <label class="form-label">Email</label>
      <input class="form-control mb-3" type="email" name="email" value="<?= e($tech['email']) ?>" required>

      <label class="form-label">Mobile Number</label>
      <input class="form-control mb-3" type="tel" name="phone" value="<?= e($tech['phone'] ?? '') ?>" maxlength="11" required>

      <label class="form-label">Details</label>
      <textarea class="form-control mb-3" name="details" rows="4"><?= e($tech['details'] ?? '') ?></textarea>

      <label class="form-label">Picture (leave empty to keep current)</label>
      <?php if (!empty($tech['picture'])): ?>
        <div class="mb-2"><img src="../<?= e($tech['picture']) ?>" style="width:80px;height:80px;object-fit:cover" class="rounded" alt=""></div>
      <?php endif; ?>
      <input class="form-control mb-3" type="file" name="picture" accept="image/*">

      <label class="form-label">Certificate (leave empty to keep current)</label>
      <?php if (!empty($tech['certificate'])): ?>
        <div class="mb-2"><a href="../<?= e($tech['certificate']) ?>" target="_blank">View current certificate</a></div>
      <?php endif; ?>
      <input class="form-control mb-3" type="file" name="certificate" accept=".pdf,image/*">

      <div class="d-flex gap-2">
        <button class="btn btn-primary flex-grow-1">Save Changes</button>
        <a href="technicians.php" class="btn btn-light">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php require '../partials/footer.php'; ?>