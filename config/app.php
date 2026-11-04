<?php
session_start();
require_once __DIR__ . '/database.php';

function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function redirect($url) { header("Location: $url"); exit; }
function flash($type, $message) { $_SESSION['flash'] = [$type, $message]; }
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        [$type,$message] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-'.e($type).' alert-dismissible fade show" role="alert">'.e($message).'<button class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
function logged_in() { return !empty($_SESSION['user']); }
function require_login() { if (!logged_in()) redirect('login.php'); }
function require_role($role) {
    require_login();
    $allowed = is_array($role) ? $role : [$role];
    if (!in_array($_SESSION['user']['role'], $allowed, true)) { http_response_code(403); exit('Access denied.'); }
}
function user() { return $_SESSION['user'] ?? null; }
function go_dashboard() {
    if (!logged_in()) redirect('login.php');
    $role = $_SESSION['user']['role'];
    redirect($role === 'admin' ? 'admin/dashboard.php' : ($role === 'technician' ? 'technician/dashboard.php' : ($role === 'staff' ? 'staff/dashboard.php' : 'patient/dashboard.php')));
}
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Invalid request token.'); }
}
function money($n) { return '₹'.number_format((float)$n,2); }
