

<?php require_once __DIR__ . '/../config/app.php'; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'DiagnosCare') ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><link href="<?= $basePath ?? '' ?>assets/css/style.css" rel="stylesheet"></head><body>
<nav class="navbar navbar-expand-lg border-bottom sticky-top"><div class="container"><a class="navbar-brand fw-bold d-flex align-items-center" href="<?= $basePath ?? '' ?>index.php"><span class="brand-mark"><i class="bi bi-heart-pulse-fill"></i></span>DiagnosCare</a><button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
<li><a class="nav-link" href="<?= $basePath ?? '' ?>about.php"><i class="bi bi-building me-1"></i>About</a></li>
<?php if(logged_in()): ?>
<li><a class="nav-link" href="/diagnostic_lab/<?= user()['role']==='admin'?'admin/dashboard.php':(user()['role']==='technician'?'technician/dashboard.php':(user()['role']==='staff'?'staff/dashboard.php':'patient/dashboard.php')) ?>"><i class="bi bi-grid me-1"></i>Dashboard</a></li>
<?php if(user()['role']==='admin'): ?><li><a class="nav-link" href="/diagnostic_lab/admin/technicians.php"><i class="bi bi-people me-1"></i>Technicians</a></li><?php endif; ?>
<li><a class="btn btn-outline-primary btn-sm" href="<?= $basePath ?? '' ?>logout.php">Logout</a></li>
<?php else: ?>
<li><a class="nav-link" href="<?= $basePath ?? '' ?>index.php#tests">Tests</a></li>
<li><a class="nav-link" href="<?= $basePath ?? '' ?>index.php#reviews">Reviews</a></li>
<li><a class="nav-link" href="<?= $basePath ?? '' ?>login.php">Login</a></li>
<li><a class="btn btn-primary btn-sm" href="<?= $basePath ?? '' ?>register.php">Sign in</a></li>
<?php endif; ?>
</ul></div></div></nav><main class="container py-4"><?php show_flash(); ?>
