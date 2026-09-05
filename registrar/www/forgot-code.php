<?php
/**
 * forgot-code.php — Retrieve tracking code by name + student ID + birthdate
 *
 * @author   rdelacruz
 * @modified 2025-01-25
 */
require_once 'includes/db.php';

$page_title = 'Forgot Tracking Code';
$active_nav = 'home';

$results = [];
$searched = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $surname    = trim($_POST['surname']    ?? '');

    if (!$student_id && !$surname) {
        $error = 'Please enter at least your Student ID or Surname.';
    } else {
        $searched = true;

        // Prepared statement — safe
        if ($student_id && $surname) {
            $stmt = $conn->prepare(
                "SELECT tracking_code, doc_type, status, requested_at
                 FROM document_requests
                 WHERE student_id_no = ? AND applicant_name LIKE ?
                 ORDER BY requested_at DESC"
            );
            $like = '%' . $surname . '%';
            $stmt->bind_param('ss', $student_id, $like);
        } elseif ($student_id) {
            $stmt = $conn->prepare(
                "SELECT tracking_code, doc_type, status, requested_at
                 FROM document_requests WHERE student_id_no = ?
                 ORDER BY requested_at DESC"
            );
            $stmt->bind_param('s', $student_id);
        } else {
            $stmt = $conn->prepare(
                "SELECT tracking_code, doc_type, status, requested_at
                 FROM document_requests WHERE applicant_name LIKE ?
                 ORDER BY requested_at DESC"
            );
            $like = '%' . $surname . '%';
            $stmt->bind_param('s', $like);
        }

        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

require_once 'includes/public_header.php';
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/">Home</a><span>&rsaquo;</span><span>Forgot Tracking Code</span>
  </div>
  <div class="page-title">
    <h2>Forgot Tracking Code</h2>
    <p>Enter your Student ID or Surname to retrieve your tracking code.</p>
  </div>

  <div class="panel" style="max-width:560px;">
    <div class="panel-head">Retrieve Tracking Code</div>
    <div class="panel-body">
      <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/forgot-code.php">
        <div class="form-group">
          <label for="student_id">Student ID Number</label>
          <input type="text" id="student_id" name="student_id" class="form-control"
                 placeholder="e.g. 2021-00042"
                 value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="surname">Surname</label>
          <input type="text" id="surname" name="surname" class="form-control"
                 placeholder="e.g. Santos"
                 value="<?= htmlspecialchars($_POST['surname'] ?? '') ?>">
          <p class="hint">Enter at least one field. Both fields narrow the results.</p>
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="/" class="btn btn-secondary" style="margin-left:8px;">Back</a>
      </form>
    </div>
  </div>

  <?php if ($searched): ?>
  <div class="panel" style="margin-top:16px;">
    <div class="panel-head">Results &mdash; <?= count($results) ?> found</div>
    <div class="panel-body" style="padding:0;">
      <?php if (empty($results)): ?>
      <div style="padding:20px;text-align:center;color:#888;">No requests found. Check your details and try again.</div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr><th>Tracking Code</th><th>Document</th><th>Status</th><th>Submitted</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($results as $r): ?>
          <tr>
            <td><span class="ref-no"><?= htmlspecialchars($r['tracking_code']) ?></span></td>
            <td><?= htmlspecialchars($r['doc_type']) ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td style="font-size:12px;"><?= date('M j, Y', strtotime($r['requested_at'])) ?></td>
            <td><a href="/?q=<?= urlencode($r['tracking_code']) ?>" class="btn btn-secondary btn-sm">Track</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
