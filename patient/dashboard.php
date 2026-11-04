<?php
require '../config/app.php'; require_role('patient');
$title='Patient Dashboard'; $basePath='../'; $uid=user()['id'];

$st=$pdo->prepare("SELECT a.*,t.name test_name,t.category,t.price,t.duration_minutes,p.status payment_status,p.method,p.transaction_id,p.paid_at,r.result_status,r.result_text,r.updated_at result_updated, rep.id report_id,rep.report_title,rep.report_content,rep.created_at report_created, tech.name technician_name,tech.email technician_email,tech.phone technician_phone FROM appointments a JOIN diagnostic_tests t ON t.id=a.test_id LEFT JOIN payments p ON p.appointment_id=a.id LEFT JOIN test_results r ON r.appointment_id=a.id LEFT JOIN reports rep ON rep.appointment_id=a.id LEFT JOIN users tech ON tech.id=a.technician_id WHERE a.patient_id=? ORDER BY a.appointment_date DESC,a.appointment_time DESC");
$st->execute([$uid]); $appointments=$st->fetchAll();

$upcoming=array_values(array_filter($appointments,function($a){return $a['status']!=='Cancelled' && strtotime($a['appointment_date'].' '.$a['appointment_time'])>=time();}));
$next=$upcoming[0]??null;
$paidTotal=0; $pendingCount=0; $completedCount=0; $reportCount=0;
foreach($appointments as $a){ if(($a['payment_status']??'')==='Paid') $paidTotal+=(float)$a['price']; if(($a['payment_status']??'Pending')!=='Paid' && $a['status']!=='Cancelled') $pendingCount++; if($a['status']==='Completed') $completedCount++; if(!empty($a['report_id'])) $reportCount++; }

require '../partials/header.php'; ?>
<div class="dashboard-layout">
<aside class="side-nav card p-2">
<a class="active" href="dashboard.php"><i class="bi bi-grid"></i> Overview</a>
<a href="book_appointment.php"><i class="bi bi-calendar-plus"></i> Book Test</a>
<a href="#appointments"><i class="bi bi-calendar-check"></i> Appointments</a>
<a href="#reports"><i class="bi bi-file-earmark-medical"></i> Reports</a>
<a href="#profile"><i class="bi bi-person-circle"></i> My Profile</a>
</aside>
<section>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
<div><span class="dashboard-eyebrow"><i class="bi bi-shield-check"></i> Secure patient portal</span><h2 class="dashboard-title mb-1 mt-2">Good to see you, <?=e(user()['name'])?> 👋</h2><p class="text-muted mb-0">Your appointments, payments and lab reports — all in one place.</p></div>
<a class="btn btn-primary" href="book_appointment.php"><i class="bi bi-plus-lg"></i> Book New Test</a>
</div>

<div class="row g-3 mb-4">
<div class="col-sm-6 col-xl-3"><div class="card stat-card metric-card hover-lift"><div class="stat-icon mb-2"><i class="bi bi-calendar-check"></i></div><h4><?=count($appointments)?></h4><span class="text-muted">Total appointments</span></div></div>
<div class="col-sm-6 col-xl-3"><div class="card stat-card metric-card hover-lift"><div class="stat-icon mb-2"><i class="bi bi-hourglass-split"></i></div><h4><?=$pendingCount?></h4><span class="text-muted">Payment pending</span></div></div>
<div class="col-sm-6 col-xl-3"><div class="card stat-card metric-card hover-lift"><div class="stat-icon mb-2"><i class="bi bi-currency-rupee"></i></div><h4>₹<?=number_format($paidTotal,0)?></h4><span class="text-muted">Paid for tests</span></div></div>
<div class="col-sm-6 col-xl-3"><div class="card stat-card metric-card hover-lift"><div class="stat-icon mb-2"><i class="bi bi-file-earmark-medical"></i></div><h4><?=$reportCount?></h4><span class="text-muted">Reports available</span></div></div>
</div>

<div class="row g-4 mb-4">
<div class="col-lg-7">
<div class="card p-4 h-100 upcoming-card">
<div class="d-flex justify-content-between align-items-start gap-3"><div><span class="section-kicker"><i class="bi bi-calendar2-week"></i> Next appointment</span><h4 class="mt-2 mb-1"><?= $next ? e($next['test_name']) : 'No upcoming appointment' ?></h4><p class="text-muted mb-0"><?= $next ? e($next['category']) : 'Book a diagnostic test whenever you are ready.' ?></p></div><?php if($next): ?><span class="status-pill status-<?=strtolower(str_replace(' ','-',e($next['status'])))?>"><?=e($next['status'])?></span><?php endif; ?></div>
<?php if($next): ?>
<div class="appointment-hero mt-4">
  <div><div class="appointment-date"><strong><?php echo date('d', strtotime($next['appointment_date'])); ?></strong><span><?php echo date('M', strtotime($next['appointment_date'])); ?></span></div></div>
  <div class="flex-grow-1"><div class="fw-bold"><?php echo date('l, d F Y', strtotime($next['appointment_date'])); ?></div><div class="text-muted mt-1"><i class="bi bi-clock me-1"></i><?php echo date('h:i A', strtotime($next['appointment_time'])); ?> · <?php echo e($next['duration_minutes']); ?> min</div><div class="mt-2"><span class="payment-chip <?php echo (($next['payment_status'] ?? 'Pending') === 'Paid') ? 'paid' : 'pending'; ?>"><i class="bi bi-credit-card me-1"></i><?php echo e($next['payment_status'] ?? 'Pending'); ?></span></div></div>
  <a class="btn btn-light" href="appointment.php?id=<?php echo (int)$next['id']; ?>">View details</a>
</div>
<?php else: ?><a class="btn btn-primary mt-4" href="book_appointment.php">Schedule a test</a><?php endif; ?>
</div></div>
<div class="col-lg-5">
<div class="card p-4 h-100" id="profile"><div class="d-flex align-items-center gap-3"><div class="profile-avatar"><?=strtoupper(substr(user()['name'],0,1))?></div><div><span class="section-kicker">My profile</span><h5 class="mb-0 mt-1"><?=e(user()['name'])?></h5></div></div><div class="profile-list mt-4"><div><i class="bi bi-envelope"></i><span><?=e(user()['email'])?></span></div><div><i class="bi bi-telephone"></i><span><?=e(user()['phone']??'Phone not added')?></span></div><div><i class="bi bi-person-badge"></i><span>Patient account</span></div></div><div class="profile-note mt-3"><i class="bi bi-lock-fill"></i> Your account details are protected by the lab portal.</div></div>
</div></div>

<div class="row g-4 mb-4">
<div class="col-lg-7"><div class="card p-4 h-100"><div class="d-flex justify-content-between align-items-center mb-4"><div><span class="section-kicker">Journey tracker</span><h5 class="mb-0 mt-1">Your latest test progress</h5></div></div>
<?php $journey=$next??($appointments[0]??null); if($journey): $steps=['Booked','Confirmed','Arrived','In Progress','Completed']; $current=array_search($journey['status'],$steps); if($current===false)$current=0; ?><div class="timeline patient-timeline"><?php foreach($steps as $i=>$step): ?><div class="timeline-item <?=($i<=$current)?'done':''?>"><div class="fw-semibold"><?=e($step)?></div><div class="small text-muted"><?php if($i<$current): ?>Completed<?php elseif($i===$current): ?>Current status<?php else: ?>Waiting for this stage<?php endif; ?></div></div><?php endforeach; ?></div><?php else: ?><div class="empty-state">Your test journey will appear here after you book an appointment.</div><?php endif; ?></div></div>
<div class="col-lg-5"><div class="card p-4 h-100"><div class="d-flex justify-content-between align-items-center"><div><span class="section-kicker">Lab support</span><h5 class="mb-0 mt-1">Assigned technician</h5></div><span class="online-badge"><span class="online-dot"></span>Lab team</span></div><?php $tech=$journey??null; if($tech && !empty($tech['technician_name'])): ?><div class="tech-mini mt-4"><div class="avatar"><?=strtoupper(substr($tech['technician_name'],0,1))?></div><div class="flex-grow-1"><div class="fw-bold"><?=e($tech['technician_name'])?></div><div class="small text-muted">Diagnostic Testing</div></div></div><div class="profile-list mt-3"><div><i class="bi bi-telephone"></i><span><?=e($tech['technician_phone']??'Contact via lab')?></span></div><div><i class="bi bi-envelope"></i><span><?=e($tech['technician_email']??'Available through lab')?></span></div></div><?php else: ?><div class="empty-state py-4">A technician will be assigned by the lab team for your appointment.</div><?php endif; ?></div></div>
</div>

<div id="reports" class="card table-card p-3 mb-4"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"><div><span class="section-kicker">Records</span><h5 class="mb-0 mt-1">My appointments & reports</h5></div><input id="appointmentSearch" class="form-control" style="max-width:240px" placeholder="Search test..."></div><div class="table-responsive"><table class="table align-middle" id="appointments"><thead><tr><th>Test</th><th>Date & time</th><th>Status</th><th>Payment</th><th>Report</th><th></th></tr></thead><tbody>
<?php foreach($appointments as $a): ?><tr><td><div class="fw-semibold"><?=e($a['test_name'])?></div><small class="text-muted"><?=e($a['category'])?></small></td><td><?=e($a['appointment_date'])?><br><small class="text-muted"><?=date('h:i A',strtotime($a['appointment_time']))?></small></td><td><span class="status-pill status-<?=strtolower(str_replace(' ','-',e($a['status'])))?>"><?=e($a['status'])?></span></td><td><span class="payment-chip <?=($a['payment_status']??'Pending')==='Paid'?'paid':'pending'?>"><?=e($a['payment_status']??'Pending')?></span><?php if(($a['payment_status']??'Pending')!=='Paid' && $a['status']!=='Cancelled'): ?><a class="d-block small mt-1" href="payment.php?id=<?=$a['id']?>">Pay now</a><?php endif; ?></td><td><?php if(!empty($a['report_id'])): ?><span class="report-ready"><i class="bi bi-check-circle-fill"></i> Ready</span><?php else: ?><span class="text-muted small">Not ready</span><?php endif; ?></td><td><a class="btn btn-sm btn-light" href="appointment.php?id=<?=$a['id']?>">View</a></td></tr><?php endforeach; ?>
<?php if(!$appointments): ?><tr><td colspan="6"><div class="empty-state">No appointments yet. <a href="book_appointment.php">Book your first test</a>.</div></td></tr><?php endif; ?>
</tbody></table></div></div>

<?php $latestReport=null; foreach($appointments as $a){if(!empty($a['report_id'])){$latestReport=$a;break;}} ?>
<div class="row g-4"><div class="col-lg-8"><div class="card p-4 report-preview"><div class="d-flex justify-content-between gap-3"><div><span class="section-kicker"><i class="bi bi-file-earmark-text"></i> Latest report</span><h5 class="mt-1 mb-1"><?= $latestReport ? e($latestReport['report_title']) : 'Report preview' ?></h5><p class="text-muted small mb-0"><?= $latestReport ? 'Generated '.date('d M Y, h:i A',strtotime($latestReport['report_created'])) : 'Your report will appear here after the laboratory completes your test.' ?></p></div><?php if($latestReport): ?><span class="report-ready align-self-start"><i class="bi bi-patch-check-fill"></i> Verified</span><?php endif; ?></div><div class="report-paper mt-3"><?= $latestReport ? nl2br(e(mb_strimwidth($latestReport['report_content'],0,700,'...'))) : '<div class="text-muted text-center py-4"><i class="bi bi-file-medical fs-2 d-block mb-2"></i>No report available yet.</div>' ?></div><?php if($latestReport): ?><a class="btn btn-outline-primary mt-3" href="appointment.php?id=<?=$latestReport['id']?>">Open full report</a><?php endif; ?></div></div>
<div class="col-lg-4"><div class="card p-4 notification-card"><span class="section-kicker"><i class="bi bi-bell"></i> Notifications</span><div class="notification-item mt-3"><span class="notification-icon"><i class="bi bi-calendar2-check"></i></span><div><div class="fw-semibold">Appointment updates</div><small class="text-muted">Check your latest booking status above.</small></div></div><div class="notification-item"><span class="notification-icon"><i class="bi bi-credit-card"></i></span><div><div class="fw-semibold">Payment reminders</div><small class="text-muted"><?= $pendingCount ? 'You have '.$pendingCount.' payment(s) waiting.' : 'All current payments are up to date.' ?></small></div></div><div class="notification-item"><span class="notification-icon"><i class="bi bi-file-earmark-medical"></i></span><div><div class="fw-semibold">Lab reports</div><small class="text-muted"><?= $reportCount ? $reportCount.' report(s) ready to view.' : 'No completed reports yet.' ?></small></div></div></div></div></div>
</section></div>
<script>document.getElementById('appointmentSearch')?.addEventListener('input',function(){let q=this.value.toLowerCase();document.querySelectorAll('#appointments tbody tr').forEach(r=>r.classList.toggle('d-none',!r.innerText.toLowerCase().includes(q)))});</script>
<?php require '../partials/footer.php'; ?>
