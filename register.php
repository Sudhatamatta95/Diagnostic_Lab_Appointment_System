<?php
require 'config/app.php';
if(logged_in()) go_dashboard();
$title='Patient Registration'; $basePath='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf(); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $pass=$_POST['password']??'';
 if(strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<6) flash('danger','Enter valid details. Password must be at least 6 characters.');
 else {
  try{$st=$pdo->prepare("INSERT INTO users(name,email,phone,password,role) VALUES(?,?,?,?, 'patient')");$st->execute([$name,$email,$phone,hash('sha256',$pass)]);flash('success','Registration successful. Please login.');redirect('login.php');}
  catch(PDOException $e){flash('danger','Email already exists.');}
 }
}
require 'partials/header.php';
?>
<div class="card login-card p-4"><h3 class="fw-bold">Create Patient Account</h3><p class="text-muted">Register to book diagnostic tests.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label class="form-label">Full Name</label><input class="form-control mb-3" name="name" required>
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required>
<label class="form-label">Phone</label><input class="form-control mb-3" name="phone" maxlength="20">
<label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" minlength="6" required>
<button class="btn btn-primary w-100">Create Account</button></form></div>
<?php require 'partials/footer.php'; ?>
