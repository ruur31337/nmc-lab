<?php
$page_title = 'How to Request Documents';
$active_nav = 'howto';
require_once 'includes/public_header.php';
?>
<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/">Home</a><span>&rsaquo;</span><span>How to Request</span>
  </div>
  <div class="page-title">
    <h2>How to Request Official Documents</h2>
    <p>Follow the steps below to submit and claim your document request online.</p>
  </div>

  <div class="two-col">
    <div class="main-col">
      <div class="panel">
        <div class="panel-head">Step-by-Step Process</div>
        <div class="panel-body">

          <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
              <h4>Settle all outstanding balances</h4>
              <p>Before submitting a request, ensure your account with the Accounting Office has no outstanding balance. Students with unpaid tuition fees or other financial obligations may experience processing delays.</p>
            </div>
          </div>

          <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
              <h4>Secure required clearances (if applicable)</h4>
              <p>For Transcript of Records (TOR) and Honorable Dismissal, obtain departmental clearance from the Library, Accounting, Student Affairs, and your department office. Attach the clearance slip when submitting online.</p>
            </div>
          </div>

          <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
              <h4>Submit your request online</h4>
              <p>Fill out the <a href="/apply.php">online request form</a>. Provide accurate personal information and select the document type. Specify the purpose of your request clearly — the registrar staff reviews this field.</p>
              <p>A <strong>tracking code</strong> (format: <code>REG-YYYY-XXXX</code>) will be issued upon successful submission. Save this code.</p>
            </div>
          </div>

          <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
              <h4>Monitor your request status</h4>
              <p>Use the <a href="/">tracking search</a> on the main portal to check your request status. Statuses are:</p>
              <ul style="margin-top:8px;">
                <li><span class="badge badge-pending">Pending</span> — Received, awaiting staff review</li>
                <li><span class="badge badge-processing">Processing</span> — Document being prepared</li>
                <li><span class="badge badge-ready">Ready</span> — Available for pickup at the Registrar's Office</li>
                <li><span class="badge badge-released">Released</span> — Document has been claimed</li>
              </ul>
            </div>
          </div>

          <div class="step-item">
            <div class="step-num">5</div>
            <div class="step-body">
              <h4>Pay the documentary stamp fee</h4>
              <p>Proceed to the Accounting Office (Window 1) to pay the required fee upon receiving a "Ready" status notification. Keep the Official Receipt (OR) — it is required when claiming.</p>
              <p style="font-size:12.5px;color:#888;">Fees vary by document type and number of copies. The registrar's office does not collect fees directly.</p>
            </div>
          </div>

          <div class="step-item">
            <div class="step-num">6</div>
            <div class="step-body">
              <h4>Claim your document at Window 3</h4>
              <p>Go to Window 3 of the Registrar's Office during office hours (Mon–Fri, 8:00 AM–5:00 PM). Present:</p>
              <ul>
                <li>Official Receipt from the Accounting Office</li>
                <li>Any valid school or government-issued ID</li>
                <li>Your tracking code (optional but speeds up processing)</li>
              </ul>
              <p style="margin-top:8px;"><strong>Third-party claiming:</strong> Present a signed authorization letter from the student, a photocopy of the student's ID, and the representative's valid ID.</p>
            </div>
          </div>

        </div>
      </div>

      <div class="panel">
        <div class="panel-head">Frequently Asked Questions</div>
        <div class="panel-body">
          <details class="faq-item">
            <summary>I forgot my tracking code. How do I recover it?</summary>
            <p>Use the <a href="/forgot-code.php">Forgot Tracking Code</a> page. Enter your Student ID number or surname to retrieve active tracking codes.</p>
          </details>
          <details class="faq-item">
            <summary>How long does processing take?</summary>
            <p>Processing times vary by document type. Certificates of Enrollment may be issued same day; Transcripts of Records take 3–5 working days. See the <a href="/apply.php">request form</a> sidebar for per-document estimates.</p>
          </details>
          <details class="faq-item">
            <summary>Can I request documents for a graduated student?</summary>
            <p>Yes. Provide the graduate's student ID number, complete name, and last year of enrollment in the form. Graduates should also secure a clearance from the Alumni Office.</p>
          </details>
          <details class="faq-item">
            <summary>What if my name or ID number on file is incorrect?</summary>
            <p>Come to the Registrar's Office in person with a valid ID and supporting documents. Data correction requests cannot be processed through the online system.</p>
          </details>
          <details class="faq-item">
            <summary>Can I request documents by email?</summary>
            <p>Walk-in and online requests are accepted. Email requests are not entertained to protect student data integrity.</p>
          </details>
        </div>
      </div>
    </div>

    <div class="side-col">
      <div class="side-card">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="/apply.php">New Document Request</a></li>
          <li><a href="/forgot-code.php">Forgot Tracking Code</a></li>
          <li><a href="/requirements.php">Document Requirements</a></li>
          <li><a href="/contact.php">Contact the Registrar</a></li>
        </ul>
      </div>
      <div class="side-card">
        <h4>Office Hours</h4>
        <p style="font-size:13px;">
          <strong>Monday–Friday</strong><br>
          8:00 AM – 5:00 PM<br>
          No lunch break<br><br>
          <strong>Closed on:</strong><br>
          Regular and special non-working holidays
        </p>
      </div>
      <div class="side-card" style="background:#fdf6e3;border:1px solid #e8c97a;">
        <h4 style="color:#7a5c00;">Note</h4>
        <p style="font-size:12.5px;color:#7a5c00;">
          The Registrar's Office reserves the right to hold document release pending verification of records or pending academic obligations.
        </p>
      </div>
    </div>
  </div>
</div>

<style>
.step-item { display:flex;gap:16px;margin-bottom:24px;align-items:flex-start; }
.step-num  { flex-shrink:0;width:32px;height:32px;border-radius:50%;background:#6B0F1A;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px; }
.step-body h4 { margin:0 0 6px;font-size:14px;color:#1a1a1a; }
.step-body p, .step-body ul { font-size:13.5px;margin-top:0; }
.faq-item { border-bottom:1px solid #e8e2dc;padding:10px 0; }
.faq-item summary { cursor:pointer;font-size:13.5px;font-weight:600;color:#6B0F1A;list-style:none; }
.faq-item summary::-webkit-details-marker { display:none; }
.faq-item p { margin:8px 0 0;font-size:13px;color:#444; }
code { background:#f0ece6;padding:1px 5px;border-radius:3px;font-size:12.5px; }
</style>

<?php require_once 'includes/footer.php'; ?>
