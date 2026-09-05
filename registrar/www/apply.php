<?php
/**
 * apply.php — Public document request application form
 *
 * @author   rdelacruz
 * @modified 2025-02-01
 */
require_once 'includes/db.php';

$page_title = 'New Document Request';
$active_nav = 'apply';

$success = false;
$error   = '';
$tracking_code = '';

$doc_types = [
    'Transcript of Records',
    'Certificate of Enrollment',
    'Certificate of Good Moral Character',
    'Diploma Authentication',
    'Course Description',
    'Honorable Dismissal',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname    = trim($_POST['surname']    ?? '');
    $given_name = trim($_POST['given_name'] ?? '');
    $middle     = trim($_POST['middle']     ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email      = trim($_POST['email']      ?? '');
    $contact    = trim($_POST['contact']    ?? '');
    $doc_type   = trim($_POST['doc_type']   ?? '');
    $copies     = (int)($_POST['copies']    ?? 1);
    $purpose    = trim($_POST['purpose']    ?? '');
    $release_date = trim($_POST['release_date'] ?? '');

    $applicant_name = trim($surname . ', ' . $given_name . ($middle ? ' ' . $middle : ''));

    if (!$surname || !$given_name) {
        $error = 'Please enter your full name.';
    } elseif (!in_array($doc_type, $doc_types)) {
        $error = 'Please select a valid document type.';
    } elseif ($copies < 1 || $copies > 10) {
        $error = 'Number of copies must be between 1 and 10.';
    } elseif ($purpose === '') {
        $error = 'Please state the purpose of your request.';
    } elseif ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Generate tracking code
        do {
            $tracking_code = 'REG-' . date('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $chk = $conn->prepare('SELECT id FROM document_requests WHERE tracking_code = ?');
            $chk->bind_param('s', $tracking_code);
            $chk->execute();
            $exists = $chk->get_result()->num_rows > 0;
            $chk->close();
        } while ($exists);

        // Only accept a full YYYY-MM-DD date — reject partial values
        $rd = ($release_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $release_date)) ? $release_date : null;

        /*
         * Basic input sanitization — strip common script injection patterns.
         * (rdelacruz, 2025-02-01)
         * TODO: replace with proper output encoding on the staff view.
         */
        $purpose = preg_replace(
            ['/<script\b[^>]*>/i', '/<\/script>/i', '/javascript\s*:/i',
             '/\bonerror\s*=/i',   '/\bonload\s*=/i'],
            '',
            $purpose
        );

        $stmt = $conn->prepare(
            'INSERT INTO document_requests
             (tracking_code, applicant_name, student_id_no, email, contact, doc_type, copies, purpose, release_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssssiss',
            $tracking_code, $applicant_name, $student_id,
            $email, $contact, $doc_type, $copies, $purpose, $rd
        );

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Failed to submit request. Please try again.';
        }
        $stmt->close();
    }
}

require_once 'includes/public_header.php';
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/">Home</a><span>&rsaquo;</span><span>New Document Request</span>
  </div>
  <div class="page-title">
    <h2>Request Official Documents</h2>
    <p>Complete the form below. A tracking code will be issued upon submission.</p>
  </div>

  <?php if ($success): ?>
  <div class="alert alert-success" style="font-size:14px;">
    <strong>Request submitted successfully.</strong><br>
    Your tracking code is: <span class="ref-no" style="font-size:16px;"><?= htmlspecialchars($tracking_code) ?></span><br><br>
    <strong>Save this code.</strong> You will need it to check the status of your request at <a href="/">the main portal</a>.<br>
    A confirmation will be sent to your email address if provided.
    <!-- TODO: email notification not yet implemented -->
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="two-col">
    <div class="main-col">
      <div class="panel">
        <div class="panel-head">Application Form</div>
        <div class="panel-body">
          <form method="POST" action="/apply.php">

            <h4 style="margin:0 0 12px;color:#6B0F1A;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">Personal Information</h4>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
              <div class="form-group">
                <label for="surname">Surname <span style="color:red">*</span></label>
                <input type="text" id="surname" name="surname" class="form-control"
                       value="<?= htmlspecialchars($_POST['surname'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="given_name">Given Name <span style="color:red">*</span></label>
                <input type="text" id="given_name" name="given_name" class="form-control"
                       value="<?= htmlspecialchars($_POST['given_name'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="middle">Middle Name</label>
                <input type="text" id="middle" name="middle" class="form-control"
                       value="<?= htmlspecialchars($_POST['middle'] ?? '') ?>">
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
              <div class="form-group">
                <label for="student_id">Student ID No.</label>
                <input type="text" id="student_id" name="student_id" class="form-control"
                       placeholder="e.g. 2021-00042"
                       value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="email">Email Address <span style="color:red">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" id="contact" name="contact" class="form-control"
                       placeholder="09XXXXXXXXX"
                       value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
              </div>
            </div>

            <hr style="margin:16px 0;border:none;border-top:1px solid #e8e2dc;">
            <h4 style="margin:0 0 12px;color:#6B0F1A;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">Document Details</h4>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;">
              <div class="form-group">
                <label for="doc_type">Document Type <span style="color:red">*</span></label>
                <select id="doc_type" name="doc_type" class="form-control" required>
                  <option value="">— Select document —</option>
                  <?php foreach ($doc_types as $dt): ?>
                  <option value="<?= htmlspecialchars($dt) ?>"
                    <?= ($_POST['doc_type'] ?? '') === $dt ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dt) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="copies">Copies <span style="color:red">*</span></label>
                <input type="number" id="copies" name="copies" class="form-control"
                       min="1" max="10" value="<?= htmlspecialchars($_POST['copies'] ?? '1') ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="purpose">Purpose / Reason for Request <span style="color:red">*</span></label>
              <textarea id="purpose" name="purpose" class="form-control" rows="4"
                        placeholder="e.g. For employment application at ABC Company. Required as part of pre-employment documents."
                        required><?= htmlspecialchars($_POST['purpose'] ?? '') ?></textarea>
              <p class="hint">Be specific. This is reviewed by the registrar staff.</p>
            </div>

            <div class="form-group">
              <label for="release_date">Preferred Release Date</label>
              <input type="date" id="release_date" name="release_date" class="form-control"
                     min="<?= date('Y-m-d', strtotime('+1 weekday')) ?>"
                     value="<?= htmlspecialchars($_POST['release_date'] ?? '') ?>"
                     style="width:200px;">
              <p class="hint">Leave blank to use standard processing time. Not guaranteed.</p>
            </div>

            <div style="background:#fdf6e3;border:1px solid #e8c97a;border-radius:4px;padding:12px 14px;margin-bottom:16px;font-size:12.5px;color:#7a5c00;">
              By submitting this form, I confirm that the information provided is accurate. I understand that false information may result in denial of my request.
            </div>

            <button type="submit" class="btn btn-primary">Submit Request</button>
            <a href="/" class="btn btn-secondary" style="margin-left:8px;">Cancel</a>

          </form>
        </div>
      </div>
    </div>

    <div class="side-col">
      <div class="side-card">
        <h4>Processing Times</h4>
        <ul>
          <li><strong>TOR:</strong> 3&ndash;5 working days</li>
          <li><strong>COE:</strong> Same day &ndash; 1 day</li>
          <li><strong>GMC:</strong> 1&ndash;2 working days</li>
          <li><strong>Diploma Auth:</strong> 5&ndash;7 working days</li>
          <li><strong>Course Desc:</strong> 3&ndash;5 working days</li>
          <li><strong>Honorable Dismissal:</strong> 3&ndash;5 working days</li>
        </ul>
      </div>
      <div class="side-card">
        <h4>Requirements</h4>
        <ul>
          <li>Cleared account balance</li>
          <li>Valid ID for claiming</li>
          <li>For TOR: departmental clearance</li>
          <li>For GMC: no pending disciplinary cases</li>
        </ul>
        <a href="/requirements.php" style="font-size:12px;">View full requirements &rsaquo;</a>
      </div>
      <div class="side-card">
        <h4>Claiming</h4>
        <p style="font-size:12.5px;">
          <strong>Window 3</strong>, Registrar's Office<br>
          Bldg A, Room 105<br>
          Mon&ndash;Fri, 8AM&ndash;5PM<br><br>
          Bring your OR and a valid ID.<br>
          Third-party claiming requires a signed authorization letter.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
