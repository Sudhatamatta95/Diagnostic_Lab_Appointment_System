<?php
require '../config/app.php';require_role('admin');$title='Manage Users';$basePath='../';$users=$pdo->query("SELECT id,name,email,phone,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();require '../partials/header.php';?>
<div class="card p-3"><h4>Users</h4><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Created</th></tr></thead><tbody><?php foreach($users as $u):?><tr><td><?=e($u['name'])?></td><td><?=e($u['email'])?></td><td><?=e($u['phone'])?></td><td><span class="badge text-bg-light"><?=e($u['role'])?></span></td><td><?=e($u['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require '../partials/footer.php'; ?>
