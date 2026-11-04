<?php
require '../config/app.php';require_role('technician');$title='Process Test';$basePath='../';$uid=user()['id'];$id=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT a.*,u.name patient_name,t.name test_name,t.price FROM appointments a JOIN users u ON u.id=a.patient_id JOIN diagnostic_tests t ON t.id=a.test_id WHERE a.id=?");$st->execute([$id]);$a=$st->fetch();if(!$a)exit('Appointment not found.');
$st=$pdo->prepare("SELECT * FROM test_results WHERE appointment_id=?");$st->execute([$id]);$r=$st->fetch();
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$sample=$_POST['sample_status'];$result=$_POST['result_status'];$text=trim($_POST['result_text']??'');
if($r){$st=$pdo->prepare("UPDATE test_results SET sample_status=?,result_status=?,result_text=?,technician_id=? WHERE appointment_id=?");$st->execute([$sample,$result,$text,$uid,$id]);}
else{$st=$pdo->prepare("INSERT INTO test_results(appointment_id,technician_id,sample_status,result_status,result_text) VALUES(?,?,?,?,?)");$st->execute([$id,$uid,$sample,$result,$text]);}
$newStatus=$result==='Completed'?'Completed':($result==='Processing'?'In Progress':'Arrived');$st=$pdo->prepare("UPDATE appointments SET technician_id=?,status=? WHERE id=?");$st->execute([$uid,$newStatus,$id]);
if($result==='Completed' && $text!==''){$st=$pdo->prepare("INSERT INTO reports(appointment_id,report_title,report_content,created_by) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE report_content=VALUES(report_content),created_by=VALUES(created_by)");$st->execute([$id,$a['test_name'].' Report',$text,$uid]);}
flash('success','Test information updated.');redirect("process.php?id=$id");}
require '../partials/header.php';?>
<div class="card p-4"><h3><?=e($a['test_name'])?></h3><p class="text-muted">Patient: <?=e($a['patient_name'])?></p>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="row g-3"><div class="col-md-6"><label class="form-label">Sample Status</label><select class="form-select" name="sample_status"><option <?=($r['sample_status']??'')==='Pending'?'selected':''?>>Pending</option><option <?=($r['sample_status']??'')==='Collected'?'selected':''?>>Collected</option><option <?=($r['sample_status']??'')==='Rejected'?'selected':''?>>Rejected</option></select></div>
<div class="col-md-6"><label class="form-label">Result Status</label><select class="form-select" name="result_status"><option <?=($r['result_status']??'')==='Pending'?'selected':''?>>Pending</option><option <?=($r['result_status']??'')==='Processing'?'selected':''?>>Processing</option><option <?=($r['result_status']??'')==='Completed'?'selected':''?>>Completed</option></select></div>
<div class="col-12"><label class="form-label">Result / Report Details</label><textarea class="form-control" name="result_text" rows="7" placeholder="Enter test observations and final result..."><?=e($r['result_text']??'')?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Result</button> <a href="dashboard.php" class="btn btn-light">Back</a></div></div></form></div>
<?php require '../partials/footer.php'; ?>
