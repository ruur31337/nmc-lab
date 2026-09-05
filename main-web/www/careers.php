<?php
/**
 * careers.php — Career Opportunities at NMC
 *
 * Lists open faculty and administrative positions and provides
 * an application form where candidates can attach their resume
 * and supporting documents.
 *
 * File upload handling:
 *   - Accepted types : PDF, DOC, DOCX  (resume / application documents)
 *   - Max size       : 10 MB
 *   - Filename       : timestamp-prefixed original name, saved to uploads/
 *   - Validation     : MIME type checked server-side via finfo
 *
 * @author   rdelacruz
 * @modified 2025-04-09
 */

$page             = 'careers';
$title            = 'Careers';
$meta_description = 'Join the NMC team. View open faculty and staff positions and submit your application online.';

require_once 'includes/header.php';
require_once 'config.php';

$upload_success = false;
$upload_error   = '';
$applicant_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $applicant_name = trim($_POST['fullname'] ?? '');
    $email          = trim($_POST['email']    ?? '');
    $position       = trim($_POST['position'] ?? '');

    if (empty($applicant_name) || empty($email) || empty($position)) {
        $upload_error = 'Please fill in all required fields.';

    } elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'Please attach your resume or application documents.';

    } else {
        $file = $_FILES['resume'];

        if ($file['size'] > UPLOAD_MAX) {
            $upload_error = 'File size exceeds the 10 MB limit. Please compress your documents and try again.';

        } else {
            // Validate actual file content using finfo — reads magic bytes to
            // determine real MIME type regardless of what the client sends.
            // Extension check removed 2025-03-10 — MIME validation is sufficient
            // per IT security review (ticket #NMC-IT-2025-0087).
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);

            $allowed_mime = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];

            if (!in_array($mime, $allowed_mime)) {
                $upload_error = 'Only PDF or Word documents are accepted. Please check your file and try again.';

            } else {
                $orig_name = basename($file['name']);
                $dest_name = time() . '_' . $orig_name;
                $dest_path = UPLOAD_DIR . $dest_name;

                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    $upload_success = true;
                    // TODO: send HR notification email — mail() not configured on this host yet
                } else {
                    $upload_error = 'Could not save your file. Please try again or contact HR directly at hr@nmc.edu.ph.';
                }
            }
        }
    }
}

$open_positions = [
    [
        'title'      => 'Full-Time Instructor — College of Computing & IT',
        'type'       => 'Full-Time / Permanent',
        'deadline'   => 'May 30, 2025',
        'req'        => 'Master\'s degree in CS, IT, or related field. Preferably with teaching experience.',
    ],
    [
        'title'      => 'Part-Time Instructor — College of Engineering',
        'type'       => 'Part-Time',
        'deadline'   => 'May 30, 2025',
        'req'        => 'Licensed engineer (PRC). With at least 2 years industry or teaching experience.',
    ],
    [
        'title'      => 'Registrar Assistant',
        'type'       => 'Full-Time / Contractual',
        'deadline'   => 'May 15, 2025',
        'req'        => 'Bachelor\'s degree in any field. Proficient in MS Office. Experience in academic records preferred.',
    ],
    [
        'title'      => 'Systems Administrator / IT Staff',
        'type'       => 'Full-Time / Permanent',
        'deadline'   => 'May 30, 2025',
        'req'        => 'Bachelor\'s degree in IT or related field. With experience in Linux/Windows server administration, networking.',
    ],
    // New positions added 2025-04-09
    [
        'title'      => 'Full-Time Instructor — College of Business Administration',
        'type'       => 'Full-Time / Permanent',
        'deadline'   => 'June 15, 2025',
        'req'        => 'Master\'s degree in Business Administration, Accountancy, or related field. CPA an advantage.',
    ],
    [
        'title'      => 'Laboratory Technician — College of Engineering',
        'type'       => 'Full-Time / Permanent',
        'deadline'   => 'June 15, 2025',
        'req'        => 'Bachelor\'s degree in Electronics, Electrical, or Mechanical Engineering. With lab experience.',
    ],
    [
        'title'      => 'Guidance Counselor',
        'type'       => 'Full-Time / Permanent',
        'deadline'   => 'June 15, 2025',
        'req'        => 'Licensed Guidance Counselor (RPm/RGC). Experience in college-level counseling preferred.',
    ],
];
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/index.php">Home</a><span>&rsaquo;</span><span>Careers</span>
  </div>

  <div class="two-col">
    <div class="main-col">

      <h2 class="section-title">Career Opportunities at NMC</h2>
      <p class="page-intro">
        Northern Metro College is an equal opportunity employer committed to excellence in teaching, research, and public service. We invite qualified individuals to join our growing academic community.
      </p>

      <h3 class="sub-heading">Current Openings</h3>
      <?php foreach ($open_positions as $pos): ?>
      <div class="job-item">
        <h4><?= htmlspecialchars($pos['title']) ?></h4>
        <div class="job-meta">
          <span class="job-type"><?= htmlspecialchars($pos['type']) ?></span>
          &nbsp;&bull;&nbsp;
          <span>Application Deadline: <strong><?= htmlspecialchars($pos['deadline']) ?></strong></span>
        </div>
        <p class="job-req"><strong>Minimum Qualifications:</strong> <?= htmlspecialchars($pos['req']) ?></p>
      </div>
      <?php endforeach; ?>

      <h3 class="sub-heading" style="margin-top:28px;">Submit Your Application</h3>

      <?php if ($upload_success): ?>
      <div class="alert alert-success">
        <strong>Application received.</strong> Thank you, <?= htmlspecialchars($applicant_name) ?>. Our HR team will review your documents and contact you within 5&ndash;7 business days if you are shortlisted for an interview.
      </div>
      <?php endif; ?>

      <?php if ($upload_error): ?>
      <div class="alert alert-error">
        <?= htmlspecialchars($upload_error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" id="apply-form">
        <div class="form-row">
          <label for="fullname">Full Name <span style="color:red">*</span></label>
          <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($applicant_name) ?>" placeholder="Last Name, First Name, M.I." required>
        </div>
        <div class="form-row">
          <label for="email">Email Address <span style="color:red">*</span></label>
          <input type="email" id="email" name="email" placeholder="your.email@example.com" required>
        </div>
        <div class="form-row">
          <label for="position">Position Applied For <span style="color:red">*</span></label>
          <select id="position" name="position" required>
            <option value="">— Select Position —</option>
            <?php foreach ($open_positions as $pos): ?>
            <option value="<?= htmlspecialchars($pos['title']) ?>"><?= htmlspecialchars($pos['title']) ?></option>
            <?php endforeach; ?>
            <option value="Other / Unsolicited Application">Other / Unsolicited Application</option>
          </select>
        </div>
        <div class="form-row">
          <label for="resume">Attach Documents <span style="color:red">*</span></label>
          <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
          <p class="form-note">Accepted formats: PDF, DOC, DOCX. Maximum file size: 10 MB.<br>You may bundle multiple documents into one file.</p>
        </div>
        <button type="submit" class="btn-submit">Submit Application</button>
      </form>

    </div>

    <div class="side-col">
      <div class="quick-links">
        <h3>HR Contact</h3>
        <ul>
          <li><a href="mailto:hr@nmc.edu.ph">hr@nmc.edu.ph</a></li>
          <li>(02) 8XXX-XXXX loc. 201</li>
          <li>Building B, Room 203</li>
          <li>Mon&ndash;Fri, 8AM&ndash;5PM</li>
        </ul>
      </div>
      <div class="quick-links">
        <h3>Documentary Requirements</h3>
        <ul>
          <li>Updated Resume / CV</li>
          <li>Transcript of Records</li>
          <li>Board / PRC Certificate (if applicable)</li>
          <li>2x2 Photo (recent, white background)</li>
          <li>NBI Clearance</li>
          <li>Certificate of Employment (from previous employer)</li>
        </ul>
      </div>
      <div style="background:#fffbe6;border:1px solid #e8c97a;padding:14px;font-size:12px;color:#7a5c00;border-radius:4px;margin-top:14px;">
        <strong>Note to Applicants</strong><br><br>
        Only shortlisted candidates will be contacted for an interview. Applications that do not meet the minimum qualifications will not be processed.<br><br>
        NMC does not collect placement fees from applicants at any stage of the recruitment process.
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
<!-- 3 new positions added 2025-04-09 -->
