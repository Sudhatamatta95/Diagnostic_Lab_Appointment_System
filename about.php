<?php
require 'config/app.php';
$title='About DiagnosCare | Team & Laboratory'; $basePath='';
$techs=$pdo->query("SELECT u.id,u.name,u.email,u.phone,t.details,t.picture,t.certificate FROM users u LEFT JOIN technicians t ON t.user_id=u.id WHERE u.role='technician' ORDER BY u.name")->fetchAll();
$bios=[
 'Lead Diagnostic Technician — Hematology & Sample Processing',
 'Senior Laboratory Technician — Biochemistry & Quality Control',
 'Clinical Laboratory Technician — Pathology & Reporting'
];
require 'partials/header.php';
?>
<section class="about-hero hero mb-5"><div class="row align-items-center g-4"><div class="col-lg-7"><span class="hero-badge"><i class="bi bi-award"></i> People, technology & trust</span><h1 class="fw-bold mt-3">Meet the team behind every test.</h1><p class="lead opacity-75">Established in 2018, DiagnosCare Diagnostic Laboratory was founded with the vision of providing accurate, reliable, and accessible diagnostic testing services. Over the years, the laboratory has expanded its testing facilities, technology, and patient services while maintaining a strong focus on quality and timely reporting.</p><a href="index.php#contact" class="btn btn-light">Visit / Contact the Lab</a></div><div class="col-lg-5 text-center"><img src="assets/images/lab.jpg" class="about-image" alt="Diagnostic laboratory infrastructure"></div></div></section>

<section class="mb-5">
  <div class="text-center mb-4">
    <span class="text-primary fw-semibold">OUR TECHNICIANS</span>
    <h2 class="section-title mt-2">Qualified laboratory professionals</h2>
  </div>

  <div class="row g-4">
  <?php foreach($techs as $i=>$t):
    $doneSt=$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE technician_id=? AND status='Completed'");
    $doneSt->execute([$t['id']]);
    $done=(int)$doneSt->fetchColumn();
    $bio=!empty($t['details'])?$t['details']:$bios[$i%count($bios)];
    $img=!empty($t['picture'])?$t['picture']:'assets/images/technician-'.(($i%3)+1).'.svg';
  ?>
    <div class="col-lg-6">
      <div class="card p-3 h-100 hover-lift">

        <div class="row g-3 align-items-center">
          <div class="col-sm-5">
            <img src="<?=e($img)?>" class="rounded-4" style="width:100%;height:220px;object-fit:cover" alt="Technician profile image">
          </div>
          <div class="col-sm-7">
            <span class="badge bg-light text-primary mb-2">Laboratory Technician</span>
            <h4 class="mb-1"><?=e($t['name'])?></h4>
            <p class="text-muted small mb-3"><?=e($bio)?></p>
            <div class="row g-2 small">
              <div class="col-6"><div class="p-2 bg-light rounded-3"><strong><?=$done?></strong><br><span class="text-muted">Tests completed</span></div></div>
              <div class="col-6"><div class="p-2 bg-light rounded-3"><strong>5+</strong><br><span class="text-muted">Years experience*</span></div></div>
            </div>
            <div class="mt-3 small">
              <i class="bi bi-envelope me-1 text-primary"></i><?=e($t['email'])?><br>
              <i class="bi bi-telephone me-1 text-primary"></i><?=e($t['phone'] ?: 'Contact through lab')?>
            </div>
          </div>
        </div>

        <div class="border-top mt-3 pt-3">
          <strong>Certificate &amp; recognition</strong>
          <div class="row g-2 mt-1">
          <?php if(!empty($t['certificate'])): $isPdf=strtolower(pathinfo($t['certificate'],PATHINFO_EXTENSION))==='pdf'; ?>
            <div class="col-12">
              <?php if($isPdf): ?>
                <a href="<?=e($t['certificate'])?>" target="_blank" class="btn btn-light"><i class="bi bi-file-earmark-pdf me-1"></i>View certificate (PDF)</a>
              <?php else: ?>
                <a href="<?=e($t['certificate'])?>" target="_blank">
                  <img src="<?=e($t['certificate'])?>" class="rounded-3 border" style="width:100%;height:auto;max-height:280px;object-fit:contain;background:#f8f9fa" alt="Technician certificate">
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="col-6"><img src="assets/images/certificate-<?=($i%2)+1?>.svg" class="gallery-img" alt="Technician certificate"></div>
            <div class="col-6"><img src="assets/images/technician-award-<?=($i%2)+1?>.svg" class="gallery-img" alt="Technician award event"></div>
          <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  <?php endforeach; ?>

  <?php if(!$techs): ?>
    <div class="col-12"><div class="empty-state card">No technician accounts found. Add technicians from the admin side to populate this section.</div></div>
  <?php endif; ?>
  </div>
</section>

<section class="mb-5"><div class="text-center mb-4"><span class="text-primary fw-semibold">LAB INFRASTRUCTURE</span><h2 class="section-title mt-2">A modern testing environment</h2></div><div class="row g-3"><div class="col-md-6 col-lg-4"><div class="card overflow-hidden h-100"><img src="assets/images/Reception_collection.jpg" class="gallery-wide" alt="Laboratory reception and infrastructure"><div class="p-3"><h5>Reception & collection</h5><p class="text-muted small mb-0">A clean patient-facing area for registration, sample collection and appointment support.</p></div></div></div><div class="col-md-6 col-lg-4"><div class="card overflow-hidden h-100"><img src="assets/images/patient.jpg" class="gallery-wide" alt="Patient diagnostic testing"><div class="p-3"><h5>Patient testing</h5><p class="text-muted small mb-0">Our patient testing area provides a safe, clean, and comfortable environment for sample collection and diagnostic testing. Trained laboratory staff follow proper procedures while collecting and handling samples to ensure accurate and reliable results.</p></div></div></div><div class="col-md-6 col-lg-4"><div class="card overflow-hidden h-100"><img src="assets/images/Quality.jpg" class="gallery-wide" alt="Laboratory award"><div class="p-3"><h5>Quality recognition</h5><p class="text-muted small mb-0">Our laboratory is committed to maintaining high standards of quality, accuracy, and reliability in diagnostic testing. Our quality recognition section highlights our commitment to professional laboratory practices, accurate reporting, patient safety, and continuous improvement.</p></div></div></div></div></section>

<section id="contact" class="card p-4 p-lg-5 mb-4"><div class="row g-4 align-items-stretch"><div class="col-lg-5"><span class="text-primary fw-semibold">CONTACT & LOCATION</span><h2 class="section-title mt-2">Find our laboratory</h2><p class="text-muted">Address — replace this with your actual laboratory address before submission or deployment.</p><div class="contact-list"><div><i class="bi bi-geo-alt-fill"></i><span>DiagnosCare Diagnostic Laboratory<br>Shivajinagar, Pune, Maharashtra 411005</span></div><div><i class="bi bi-telephone-fill"></i><span>+91 98765 43210</span></div><div><i class="bi bi-envelope-fill"></i><span>support@diagnoscare.test</span></div><div><i class="bi bi-clock-fill"></i><span>Mon–Sat · 8:00 AM – 8:00 PM</span></div></div><a class="btn btn-primary mt-3" target="_blank" href="https://www.google.com/maps/search/?api=1&query=Shivajinagar%20Pune">Open in Google Maps <i class="bi bi-arrow-up-right ms-1"></i></a></div><div class="col-lg-7"><div class="map-wrap"><iframe title="DiagnosCare location map" src="https://www.google.com/maps?q=Shivajinagar%20Pune&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div></div></div></section>
<?php require 'partials/footer.php'; ?>