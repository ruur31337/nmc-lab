<?php
$page_title = 'Contact the Registrar';
$active_nav = 'contact';
require_once 'includes/public_header.php';
?>
<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/">Home</a><span>&rsaquo;</span><span>Contact</span>
  </div>
  <div class="page-title">
    <h2>Contact the Registrar's Office</h2>
    <p>For inquiries not covered by the online system, contact us through the channels below.</p>
  </div>

  <div class="two-col">
    <div class="main-col">
      <div class="panel">
        <div class="panel-head">Office Information</div>
        <div class="panel-body">
          <div class="contact-grid">
            <div class="contact-item">
              <div class="contact-label">Office</div>
              <div class="contact-val">Office of the Registrar<br>Northern Metro College</div>
            </div>
            <div class="contact-item">
              <div class="contact-label">Location</div>
              <div class="contact-val">Building A, Room 105<br>NMC Main Campus, Quezon City</div>
            </div>
            <div class="contact-item">
              <div class="contact-label">Office Hours</div>
              <div class="contact-val">Monday – Friday<br>8:00 AM – 5:00 PM (no lunch break)<br><span style="font-size:11.5px;color:#888;">Closed on holidays</span></div>
            </div>
            <div class="contact-item">
              <div class="contact-label">Local Extension</div>
              <div class="contact-val">loc. 110 (Registrar's Office)<br>loc. 111 (Records Section)</div>
            </div>
            <div class="contact-item">
              <div class="contact-label">Email</div>
              <div class="contact-val">registrar@nmc.edu.ph<br><span style="font-size:11.5px;color:#888;">Replies within 1–2 working days. Do not send document requests via email.</span></div>
            </div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">Window Guide</div>
        <div class="panel-body" style="padding:0;">
          <table class="data-table">
            <thead><tr><th>Window</th><th>Purpose</th></tr></thead>
            <tbody>
              <tr><td><strong>Window 1</strong></td><td>Walk-in requests, inquiries, enrollment verification</td></tr>
              <tr><td><strong>Window 2</strong></td><td>Records requests, clearance processing</td></tr>
              <tr><td><strong>Window 3</strong></td><td>Document release (claim here — bring OR and ID)</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="panel">
        <div class="panel-head">Frequently Contacted For</div>
        <div class="panel-body">
          <ul style="font-size:13.5px;">
            <li>Status of submitted document requests → <a href="/">use the tracking portal</a></li>
            <li>Forgotten tracking codes → <a href="/forgot-code.php">retrieve your code here</a></li>
            <li>Name corrections or enrollment discrepancies → come in person with supporting documents</li>
            <li>Rush processing inquiries → call loc. 110 directly; not guaranteed</li>
            <li>Request for certification for scholarship renewals → <a href="/apply.php">submit online</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="side-col">
      <div class="side-card" style="background:#6B0F1A;color:#fff;">
        <h4 style="color:#C9961A;margin-top:0;">Drop a Message</h4>
        <p style="font-size:13px;opacity:.9;">For general inquiries, email us at:</p>
        <p style="font-size:14px;font-weight:700;">registrar@nmc.edu.ph</p>
        <p style="font-size:12px;opacity:.7;">Do NOT send document requests by email. Use the <a href="/apply.php" style="color:#C9961A;">online form</a>.</p>
      </div>
      <div class="side-card">
        <h4>Related Offices</h4>
        <ul style="font-size:13px;">
          <li><strong>Accounting Office</strong> — loc. 120 (fees &amp; OR)</li>
          <li><strong>Admissions</strong> — loc. 130 (new students, transfer)</li>
          <li><strong>Student Affairs</strong> — loc. 140 (clearance, GMC)</li>
          <li><strong>Alumni Office</strong> — loc. 150 (graduated students)</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<style>
.contact-grid { display:grid;gap:14px; }
.contact-item { padding-bottom:14px;border-bottom:1px solid #f0ece6; }
.contact-item:last-child { border-bottom:none;padding-bottom:0; }
.contact-label { font-size:11.5px;text-transform:uppercase;letter-spacing:.5px;color:#6B0F1A;font-weight:700;margin-bottom:3px; }
.contact-val { font-size:13.5px;color:#333; }
@media (prefers-color-scheme: dark) {
  .contact-item { border-bottom-color:#444; }
  .contact-val { color:#ccc; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
