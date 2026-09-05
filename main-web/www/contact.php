<?php
/**
 * contact.php — Contact Page
 *
 * @author   rdelacruz
 * @modified 2025-01-15
 */
$page             = 'contact';
$title            = 'Contact Us';
$meta_description = 'Get in touch with Northern Metro College.';
require_once 'includes/header.php';
?>
<div class="page-wrap">
  <div class="breadcrumb"><a href="/index.php">Home</a><span>&rsaquo;</span><span>Contact Us</span></div>
  <div class="two-col">
    <div class="main-col">
      <h2 class="section-title">Contact Us</h2>
      <p class="page-intro">For inquiries, please reach out to the appropriate office or send us an email.</p>
      <h3 class="sub-heading">Department Directory</h3>
      <table class="data-table">
        <thead><tr><th>Office</th><th>Email</th><th>Local</th></tr></thead>
        <tbody>
          <tr><td>Office of the President</td><td>president@nmc.edu.ph</td><td>loc. 100</td></tr>
          <tr><td>Office of the Registrar</td><td>registrar@nmc.edu.ph</td><td>loc. 110</td></tr>
          <tr><td>Admissions Office</td><td>admissions@nmc.edu.ph</td><td>loc. 115</td></tr>
          <tr><td>Human Resources</td><td>hr@nmc.edu.ph</td><td>loc. 201</td></tr>
          <tr><td>IT Helpdesk</td><td>ithelpdesk@nmc.edu.ph</td><td>loc. 305</td></tr>
          <tr><td>Finance / Cashier</td><td>finance@nmc.edu.ph</td><td>loc. 120</td></tr>
        </tbody>
      </table>
    </div>
    <div class="side-col">
      <div class="quick-links">
        <h3>Main Campus</h3>
        <ul>
          <li>123 NMC Avenue, Taguig City</li>
          <li>Tel: (02) 8XXX-XXXX</li>
          <li>Email: info@nmc.edu.ph</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
<!-- campus map: Google Maps embed pending API key approval — 2025-01-22 -->
<!-- registrar hotline updated 2025-04-01 -->
