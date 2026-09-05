<?php
/**
 * programs.php — Academic Programs
 *
 * Lists all undergraduate and graduate programs offered by NMC,
 * grouped by college.
 *
 * @author   rdelacruz
 * @modified 2025-02-26
 */

$page             = 'programs';
$title            = 'Academic Programs';
$meta_description = 'Explore undergraduate and graduate programs at Northern Metro College — Engineering, Computing, Business, Education, and Health Sciences.';

require_once 'includes/header.php';
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/index.php">Home</a><span>&rsaquo;</span><span>Academic Programs</span>
  </div>

  <div class="two-col">
    <div class="main-col">

      <h2 class="section-title">Academic Programs</h2>
      <p class="page-intro">
        Northern Metro College offers 32 degree programs across six colleges, ranging from four-year undergraduate degrees to graduate and post-graduate programs. All programs are recognized by the Commission on Higher Education (CHED).
      </p>

      <h3 class="sub-heading">College of Computing and Information Technology</h3>
      <table class="data-table">
        <thead><tr><th>Program</th><th>Degree</th><th>Units</th><th>Years</th></tr></thead>
        <tbody>
          <tr><td>BS Computer Science</td><td>BSCS</td><td>138</td><td>4</td></tr>
          <tr><td>BS Information Technology</td><td>BSIT</td><td>138</td><td>4</td></tr>
          <tr><td>BS Information Systems</td><td>BSIS</td><td>138</td><td>4</td></tr>
          <tr><td>Associate in Computer Technology</td><td>ACT</td><td>72</td><td>2</td></tr>
          <tr><td>Master of Science in Computer Science</td><td>MSCS</td><td>48</td><td>2</td></tr>
        </tbody>
      </table>

      <h3 class="sub-heading">College of Engineering</h3>
      <table class="data-table">
        <thead><tr><th>Program</th><th>Degree</th><th>Units</th><th>Years</th></tr></thead>
        <tbody>
          <tr><td>BS Electronics Engineering</td><td>BSECE</td><td>174</td><td>5</td></tr>
          <tr><td>BS Electrical Engineering</td><td>BSEE</td><td>174</td><td>5</td></tr>
          <tr><td>BS Civil Engineering</td><td>BSCE</td><td>174</td><td>5</td></tr>
          <tr><td>BS Mechanical Engineering</td><td>BSME</td><td>174</td><td>5</td></tr>
          <tr><td>BS Industrial Engineering</td><td>BSIE</td><td>156</td><td>4</td></tr>
        </tbody>
      </table>

      <h3 class="sub-heading">College of Business Administration</h3>
      <table class="data-table">
        <thead><tr><th>Program</th><th>Degree</th><th>Units</th><th>Years</th></tr></thead>
        <tbody>
          <tr><td>BS Business Administration — Major in Financial Management</td><td>BSBA-FM</td><td>120</td><td>4</td></tr>
          <tr><td>BS Business Administration — Major in Marketing Management</td><td>BSBA-MM</td><td>120</td><td>4</td></tr>
          <tr><td>BS Accountancy</td><td>BSA</td><td>126</td><td>4</td></tr>
          <tr><td>BS Entrepreneurship</td><td>BSEntrep</td><td>120</td><td>4</td></tr>
          <tr><td>Master in Business Administration</td><td>MBA</td><td>54</td><td>2</td></tr>
        </tbody>
      </table>

      <h3 class="sub-heading">College of Education</h3>
      <table class="data-table">
        <thead><tr><th>Program</th><th>Degree</th><th>Units</th><th>Years</th></tr></thead>
        <tbody>
          <tr><td>Bachelor of Secondary Education — Major in Mathematics</td><td>BSEd-Math</td><td>120</td><td>4</td></tr>
          <tr><td>Bachelor of Secondary Education — Major in English</td><td>BSEd-Eng</td><td>120</td><td>4</td></tr>
          <tr><td>Bachelor of Elementary Education</td><td>BEEd</td><td>120</td><td>4</td></tr>
          <tr><td>Master of Arts in Education</td><td>MAEd</td><td>48</td><td>2</td></tr>
        </tbody>
      </table>

    </div>

    <div class="side-col">
      <div class="quick-links">
        <h3>Admission Requirements</h3>
        <ul>
          <li>Completed NMC Application Form</li>
          <li>Senior High School Report Card (Form 138)</li>
          <li>Certificate of Good Moral Character</li>
          <li>PSA Birth Certificate</li>
          <li>2x2 ID Photo (2 pcs)</li>
          <li>Entrance Exam (NMC-CAT)</li>
        </ul>
      </div>
      <div class="quick-links">
        <h3>Scholarships</h3>
        <ul>
          <li><a href="#">Academic Excellence Award</a></li>
          <li><a href="#">Financial Assistance Program</a></li>
          <li><a href="#">CHED UniFAST / SUC Grants</a></li>
          <li><a href="#">TUPAD Partner Scholarship</a></li>
          <li><a href="#">Athletic Scholarship</a></li>
        </ul>
      </div>
      <div class="quick-links">
        <h3>Apply Now</h3>
        <p style="font-size:12px;color:#555;margin-bottom:10px;">Enrollment for AY 2025&ndash;2026 is open. Submit your application through the Admission Portal.</p>
        <a href="http://admission.nmc.local" class="btn-submit" style="display:block;text-align:center;font-size:13px;padding:8px;">Go to Admission Portal</a>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<!-- typos fixed 2025-04-15 -->
