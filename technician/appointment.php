<?php
require '../config/app.php';

if (!logged_in() || user()['role'] !== 'technician') {
    header('Location: ../login.php');
    exit;
}

$id     = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$techId = (int)user()['id'];

// Only the assigned technician can open this case
$st = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND technician_id = ? LIMIT 1");
$st->execute([$id, $techId]);
$appt = $st->fetch();

if (!$appt) {
    flash('danger', 'Case not found or not assigned to you.');
    header('Location: dashboard.php');
    exit;
}

// Patient details
$patient = null;
if (!empty($appt['patient_id'])) {
    $p = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    $p->execute([$appt['patient_id']]);
    $patient = $p->fetch();
}

// Test name (skipped safely if your column has a different name)
$testName = '';
try {
    if (!empty($appt['test_id'])) {
        $t = $pdo->prepare("SELECT name FROM diagnostic_tests WHERE id = ?");
        $t->execute([$appt['test_id']]);
        $testName = (string)$t->fetchColumn();
    }
} catch (PDOException $e) {}

// Upload report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $notes = trim($_POST['notes'] ?? '');
    $error = '';

    if (!isset($_FILES['report']) || $_FILES['report']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please choose a report file.';
    } else {
        $ext = strtolower(pathinfo($_FILES['report']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            $error = 'Only PDF, JPG or PNG files are allowed.';
        } elseif ($_FILES['report']['size'] > 10 * 1024 * 1024) {
            $error = 'File is too large (max 10MB).';
        }
    }

    if ($error === '') {
        $newName = uniqid('report_', true) . '.' . $ext;
        $target  = __DIR__ . '/../assets/uploads/reports/' . $newName;

        if (!move_uploaded_file($_FILES['report']['tmp_name'], $target)) {
            $error = 'Could not save the file. Check that the reports folder exists.';
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare("INSERT INTO appointment_reports (appointment_id, file_path, notes, uploaded_by) VALUES (?, ?, ?, ?)")
                    ->execute([$id, 'assets/uploads/reports/' . $newName, $notes, $techId]);
                $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?")->execute([$id]);
                $pdo->commit();
                flash('success', 'Report uploaded and case marked Completed.');
                header('Location: appointment.php?id=' . $id);
                exit;
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
    flash('danger', $error);
}

// Latest uploaded report (if any)
$r = $pdo->prepare("SELECT * FROM appointment_reports WHERE appointment_id = ? ORDER BY id DESC LIMIT 1");
$r->execute([$id]);
$report = $r->fetch();

$title = 'Case #' . $id . ' | DiagnosCare';
$basePath = '../';
require '../partials/header.php';
?>
<div class="container py-4" style="max-width:700px">
  <a href="dashboard.php" class="btn btn-light btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Back to queue</a>

  <div class="card p-4 mb-3">
    <h4 class="fw-bold mb-3">Case #<?= (int)$id ?></h4>
    <div class="row g-2">
      <div class="col-sm-6"><small class="text-muted">Patient</small><div class="fw-semibold"><?= e($patient['name'] ?? '-') ?></div></div>
      <div class="col-sm-6"><small class="text-muted">Phone</small><div><?= e($patient['phone'] ?? '-') ?></div></div>
      <div class="col-sm-6"><small class="text-muted">Test</small><div><?= e($testName ?: '-') ?></div></div>
      <div class="col-sm-6"><small class="text-muted">Status</small><div><span class="badge bg-light text-primary"><?= e($appt['status'] ?? '-') ?></span></div></div>
    </div>
  </div>

  <div class="card p-4">
    <h5 class="mb-3">Test report</h5>

    <?php if ($report): ?>
      <div class="alert alert-success">
        Report uploaded on <?= e($report['created_at']) ?>.
        <a href="../<?= e($report['file_path']) ?>" target="_blank" class="alert-link">View report</a>
        <?php if (!empty($report['notes'])): ?><div class="small mt-1">Notes: <?= e($report['notes']) ?></div><?php endif; ?>
      </div>
      <p class="text-muted small">To replace it, upload a new file below. The newest one is shown to the patient.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$id ?>">

      <label class="form-label">Report file (PDF, JPG or PNG)</label>
      <input class="form-control mb-3" type="file" name="report" accept=".pdf,image/*" required>

      <label class="form-label">Notes (optional)</label>
      <textarea class="form-control mb-3" name="notes" rows="3"></textarea>

      <button class="btn btn-primary w-100">Upload report and mark Completed</button>
    </form>
  </div>
</div>
<?php require '../partials/footer.php'; ?>