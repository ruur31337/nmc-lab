<?php
/**
 * about.php — About Northern Metro College
 *
 * Institution history, vision/mission, administration,
 * and faculty directory.
 *
 * @author   rdelacruz
 * @modified 2025-03-04
 */

$page             = 'about';
$title            = 'About NMC';
$meta_description = 'Learn about Northern Metro College — our history, vision and mission, academic leadership, and faculty.';

require_once 'includes/header.php';
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/index.php">Home</a><span>&rsaquo;</span><span>About NMC</span>
  </div>

  <div class="two-col">
    <div class="main-col">

      <h2 class="section-title">About Northern Metro College</h2>
      <p class="page-intro">
        Northern Metro College (NMC) is a private, non-sectarian institution of higher learning founded in 1982 by Dr. Ernesto F. Navarro. Located in Taguig City, Metro Manila, NMC has grown from a small two-year technical school into a full-fledged college offering over 30 undergraduate and graduate degree programs.
      </p>

      <h3 class="sub-heading">Our History</h3>
      <p>
        NMC was established in 1982 as the Northern Metro Technical Institute, offering two-year programs in Electronics and Electrical Technology. In 1991, the school was granted college status by the Commission on Higher Education (CHED) and renamed Northern Metro College. The College of Engineering was opened in 1994, followed by the College of Business Administration in 1998, and the College of Computing and Information Technology in 2003.
      </p>
      <p style="margin-top:10px;">
        Today, NMC serves more than 8,400 students across six colleges and maintains partnerships with government agencies, industry partners, and international institutions in ASEAN and East Asia.
      </p>

      <h3 class="sub-heading">Vision &amp; Mission</h3>
      <div style="background:#f9f6f0;border-left:4px solid #6B0F1A;padding:14px 18px;margin:14px 0;">
        <p><strong>Vision:</strong> A nationally recognized college committed to holistic education, relevant research, and meaningful community engagement.</p>
        <p style="margin-top:8px;"><strong>Mission:</strong> To provide quality, accessible, and transformative higher education that develops competent, ethical, and socially responsible professionals.</p>
      </div>

      <h3 class="sub-heading">Academic Leadership</h3>
      <table class="data-table">
        <thead>
          <tr><th>Position</th><th>Name</th></tr>
        </thead>
        <tbody>
          <tr><td>College President</td><td>Dr. Maria Lourdes T. Navarro</td></tr>
          <tr><td>Executive Vice President</td><td>Atty. Ramon B. Castillo</td></tr>
          <tr><td>VP for Academic Affairs</td><td>Dr. Cynthia P. Reyes</td></tr>
          <tr><td>VP for Administration</td><td>Engr. Roberto G. Dela Cruz</td></tr>
          <tr><td>Dean, College of Computing &amp; IT</td><td>Dr. Angelo M. Santos</td></tr>
          <tr><td>Dean, College of Engineering</td><td>Engr. Florinda C. Magsino</td></tr>
          <tr><td>Dean, College of Business Admin</td><td>Dr. Josephine A. Tan</td></tr>
          <tr><td>Dean, College of Education</td><td>Dr. Remedios L. Cruz</td></tr>
          <tr><td>Registrar</td><td>Mr. Danilo F. Mercado</td></tr>
        </tbody>
      </table>

      <!-- Faculty section added 2025-01-29 -->
      <h3 class="sub-heading">Full-Time Faculty — College of Computing &amp; IT</h3>
      <table class="data-table">
        <thead>
          <tr><th>Name</th><th>Designation</th><th>Specialization</th></tr>
        </thead>
        <tbody>
          <tr><td>Dr. Angelo M. Santos</td><td>Dean / Professor</td><td>Artificial Intelligence, Machine Learning</td></tr>
          <tr><td>Assoc. Prof. Liza V. Espiritu</td><td>Associate Professor</td><td>Software Engineering, Web Systems</td></tr>
          <tr><td>Asst. Prof. Mark T. Bernardo</td><td>Assistant Professor</td><td>Network Security, Ethical Hacking</td></tr>
          <tr><td>Asst. Prof. Carla R. Navarro</td><td>Assistant Professor</td><td>Database Systems, Data Science</td></tr>
          <tr><td>Instr. Jerome D. Lim</td><td>Instructor</td><td>Web Development, UI/UX Design</td></tr>
          <tr><td>Instr. Sheila M. Ocampo</td><td>Instructor</td><td>Programming, Data Structures</td></tr>
          <!-- New hires AY 2025-2026 — added 2025-03-04 -->
          <tr><td>Instr. Paolo R. Aquino</td><td>Instructor</td><td>Mobile Development, Cloud Computing</td></tr>
          <tr><td>Instr. Michelle B. Torres</td><td>Instructor</td><td>Computer Networks, IoT</td></tr>
          <tr><td>Instr. Bernard F. Santos</td><td>Instructor</td><td>Cybersecurity, Linux Administration</td></tr>
        </tbody>
      </table>

    </div>

    <div class="side-col">
      <div class="quick-links">
        <h3>About NMC</h3>
        <ul>
          <li><a href="/about.php">History &amp; Background</a></li>
          <li><a href="/about.php#vmgo">Vision, Mission &amp; Goals</a></li>
          <li><a href="/about.php#leadership">Academic Leadership</a></li>
          <li><a href="/about.php#faculty">Faculty Directory</a></li>
          <li><a href="/programs.php">Academic Programs</a></li>
        </ul>
      </div>
      <div class="quick-links">
        <h3>Accreditation</h3>
        <ul>
          <li>CHED-recognized College</li>
          <li>PACUCOA Level III (Computing)</li>
          <li>ISO 9001:2015 Certified</li>
          <li>TESDA Accredited Programs</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<!-- faculty directory added 2025-01-29 -->
<!-- typos fixed 2025-04-15 -->
