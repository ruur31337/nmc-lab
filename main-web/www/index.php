<?php
/**
 * index.php — NMC Homepage
 *
 * Displays the hero banner, latest news and announcements,
 * quick stats, and enrollment call-to-action.
 *
 * @author   rdelacruz
 * @modified 2025-03-24
 */

$page             = 'home';
$title            = 'Home';
$meta_description = 'Northern Metro College — Quality higher education in Taguig City, Metro Manila. Offering undergraduate and graduate programs in Engineering, Computing, Business, Education, and Health Sciences.';

require_once 'includes/header.php';
?>

<div class="hero">
  <div class="hero-content">
    <h2>Shaping Leaders for a Better Tomorrow</h2>
    <p>Northern Metro College &mdash; committed to quality education, research, and community service.</p>
    <div class="hero-btns">
      <a href="http://admission.nmc.local" class="btn-hero btn-primary-hero">Apply Now</a>
      <a href="/programs.php" class="btn-hero btn-outline-hero">View Programs</a>
    </div>
  </div>
</div>

<div class="page-wrap">
  <div class="two-col">
    <div class="main-col">

      <h2 class="section-title">Latest News &amp; Updates</h2>

      <!-- Announcements — updated 2025-03-24 -->
      <div class="news-item">
        <div class="news-date">March 20, 2025</div>
        <h3><a href="#">NMC Opens Enrollment for First Semester AY 2025&ndash;2026</a></h3>
        <p>The Office of the Registrar announces that enrollment for the first semester of Academic Year 2025&ndash;2026 is now open. Returning students may enroll online through the Student Portal. New students must first complete the admission process.</p>
        <a href="http://admission.nmc.local" class="read-more">Admission Portal &rarr;</a>
      </div>

      <div class="news-item">
        <div class="news-date">March 15, 2025</div>
        <h3><a href="#">NMC College of Computing Receives CHED Recognition</a></h3>
        <p>The Commission on Higher Education (CHED) has granted Level III accreditation to the NMC College of Computing and Information Technology for its BS Computer Science and BS Information Technology programs.</p>
      </div>

      <div class="news-item">
        <div class="news-date">February 28, 2025</div>
        <h3><a href="#">Research Grant Awarded to NMC Engineering Department</a></h3>
        <p>The NMC College of Engineering received a research grant from DOST-PCIEERD for its project on sustainable urban water management. The grant covers a two-year research period beginning March 2025.</p>
      </div>

      <div class="news-item">
        <div class="news-date">February 10, 2025</div>
        <h3><a href="#">NMC Ranks Among Top 10 CALABARZON Colleges in 2025 Licensure Exam</a></h3>
        <p>NMC graduates posted a 91.4% passing rate in the February 2025 board examinations, placing the college among the top performers in the National Capital Region and nearby provinces.</p>
      </div>

    </div>

    <div class="side-col">

      <div class="quick-links">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="http://admission.nmc.local">Online Admission</a></li>
          <li><a href="http://academy.nmc.local">Student Portal</a></li>
          <li><a href="/programs.php">Academic Programs</a></li>
          <li><a href="/careers.php">Job Openings</a></li>
          <li><a href="/contact.php">Contact the Registrar</a></li>
        </ul>
      </div>

      <div class="quick-links">
        <h3>Academic Calendar</h3>
        <ul>
          <li>Enrollment: Mar 24 &ndash; Apr 18, 2025</li>
          <li>Classes Begin: June 2, 2025</li>
          <li>Midterm Exams: Aug 11&ndash;15, 2025</li>
          <li>Final Exams: Oct 20&ndash;24, 2025</li>
          <li>Sem Break: Nov 3 &ndash; Nov 21, 2025</li>
        </ul>
      </div>

      <div class="quick-links" style="background:#fdf6e3;border-color:#e8c97a;">
        <h3 style="color:#7a5c00;">Enrollment Now Open</h3>
        <p style="font-size:12px;color:#5a4200;margin-bottom:10px;">AY 2025&ndash;2026 First Semester enrollment is ongoing. Secure your slot now.</p>
        <a href="http://admission.nmc.local" class="btn-submit" style="display:block;text-align:center;font-size:13px;padding:8px;">Apply Online</a>
      </div>

    </div>
  </div>

  <div class="stat-bar">
    <div class="stat-item"><span class="stat-num">8,400+</span><span class="stat-label">Students Enrolled</span></div>
    <div class="stat-item"><span class="stat-num">420+</span><span class="stat-label">Faculty &amp; Staff</span></div>
    <div class="stat-item"><span class="stat-num">32</span><span class="stat-label">Degree Programs</span></div>
    <div class="stat-item"><span class="stat-num">42</span><span class="stat-label">Years of Excellence</span></div>
  </div>

</div>

<?php require_once 'includes/footer.php'; ?>
<!-- announcements section added 2025-03-24 -->
<!-- enrollment dates updated 2025-04-22 -->
