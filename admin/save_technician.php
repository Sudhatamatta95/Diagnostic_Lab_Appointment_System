<?php
require '../config/app.php';

if (!logged_in() || user()['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_technician.php');
    exit;
}
check_csrf();

function back($type, $msg) {
    flash($type, $msg);
    header('Location: add_technician.php');
    exit;
}

$name     = trim($_POST['name'] ?? '');
$details  = trim($_POST['details'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone    = trim($_POST['phone'] ?? '');

if ($name === '' || $email === '' || strlen($password) < 6) {
    back('danger', 'Name, email and a password (min 6 characters) are required.');
}

if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
    back('danger', 'Enter a valid 10-digit mobile number.');
}
$chk = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$chk->execute([$email]);
if ($chk->fetch()) {
    back('danger', 'This email is already registered.');
}

function uploadFile($field, $folder, $allowedExt) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        back('danger', "Invalid file type for $field.");
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        back('danger', "$field is too large (max 5MB).");
    }
    $newName = uniqid($field . '_', true) . '.' . $ext;
    $target  = __DIR__ . '/../assets/uploads/technicians/' . $folder . '/' . $newName;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        back('danger', "Could not save $field.");
    }
    return 'assets/uploads/technicians/' . $folder . '/' . $newName;
}

$picture     = uploadFile('picture', 'pictures', ['jpg', 'jpeg', 'png']);
$certificate = uploadFile('certificate', 'certificates', ['pdf', 'jpg', 'jpeg', 'png']);

try {
    $pdo->beginTransaction();

    $u = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'technician')");
    $u->execute([$name, $email, hash('sha256', $password)]);
    $userId = $pdo->lastInsertId();

$t = $pdo->prepare("INSERT INTO technicians (user_id, name, phone, details, picture, certificate) VALUES (?, ?, ?, ?, ?, ?)");
$t->execute([$userId, $name, $phone, $details, $picture, $certificate]);

    $pdo->commit();
    flash('success', 'Technician added. They can log in with the email and password you set.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('danger', 'Database error: ' . $e->getMessage());
}

header('Location: add_technician.php');
exit;