<?php
/**
 * staff/view_request.php — Request detail and status update
 *
 * @author   rdelacruz
 * @modified 2025-02-08
 *
 * NOTE: 'purpose' field rendered without htmlspecialchars().
 * Staff are trusted internal users — this view is not public-facing.
 * Sanitization was considered unnecessary for internal tools.
 * (acruz, 2025-02-08)
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

$page_title = 'View Request';
$active_nav = 'requests';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /staff/requests.php'); exit;
}

// Fetch request with processing staff name
// No JOIN to students — applicant info is stored directly in document_requests (v2 schema)
$stmt = $conn->prepare(
    'SELECT dr.*, st.full_name AS processed_by_name
     FROM document_requests dr
     LEFT JOIN nmc_staff st ON st.id = dr.processed_by
     WHERE dr.id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r) {
    header('Location: /staff/requests.php'); exit;
}

// Handle status update (POST)
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status   = trim($_POST['status']       ?? '');
    $staff_notes  = trim($_POST['staff_notes']  ?? '');
    $release_date = trim($_POST['release_date'] ?? '') ?: null;
    $valid = ['pending','processing','ready','released'];

    if (!in_array($new_status, $valid)) {
        $flash = 'error:Invalid status.';
    } else {
        $staff_id = $_SESSION['staff_id'];
        $upd = $conn->prepare(
            'UPDATE document_requests SET status=?, staff_notes=?, release_date=?, processed_by=? WHERE id=?'
        );
        $upd->bind_param('sssii', $new_status, $staff_notes, $release_date, $staff_id, $id);
        $upd->execute();
        $upd->close();
        // Re-fetch
        $stmt = $conn->prepare(
            'SELECT dr.*, st.full_name AS processed_by_name
             FROM document_requests dr
             LEFT JOIN nmc_staff st ON st.id = dr.processed_by
             WHERE dr.id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $flash = 'success:Request updated successfully.';
    }
}

require_once 'header.php';

[$flash_type, $flash_msg] = $flash ? explode(':', $flash, 2) : ['', ''];

// Show updated=1 from update_request.php redirect
if (!$flash_msg && isset($_GET['updated'])) {
    $flash_type = 'success';
    $flash_msg  = 'Request updated successfully.';
}
?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="/staff/dashboard.php">Dashboard</a><span>&rsaquo;</span>
    <a href="/staff/requests.php">Requests</a><span>&rsaquo;</span>
    <span><?= htmlspecialchars($r['tracking_code']) ?></span>
  </div>

  <?php if ($flash_msg): ?>
  <div class="alert alert-<?= $flash_type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($flash_msg) ?></div>
  <?php endif; ?>

  <div class="two-col">
    <div class="main-col">

      <!-- Request details -->
      <div class="panel">
        <div class="panel-head" style="display:flex;justify-content:space-between;">
          <span>Request Details &mdash; <span class="ref-no"><?= htmlspecialchars($r['tracking_code']) ?></span></span>
          <span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
        </div>
        <div class="panel-body">
          <table style="font-size:13.5px;width:100%;border-collapse:collapse;">
            <tr>
              <td style="width:180px;color:#888;padding:5px 0;vertical-align:top;">Document Type:</td>
              <td style="padding:5px 0;"><strong><?= htmlspecialchars($r['doc_type']) ?></strong></td>
            </tr>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Copies:</td>
              <td style="padding:5px 0;"><?= (int)$r['copies'] ?></td>
            </tr>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Date Submitted:</td>
              <td style="padding:5px 0;"><?= date('F j, Y, g:i A', strtotime($r['requested_at'])) ?></td>
            </tr>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Preferred Release:</td>
              <td style="padding:5px 0;">
                <?= $r['release_date'] ? date('F j, Y', strtotime($r['release_date'])) : '<span style="color:#aaa;">Not specified</span>' ?>
              </td>
            </tr>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Purpose:</td>
              <td style="padding:5px 0;">
                <?php
                /*
                 * Developer note (acruz, 2025-02-08):
                 * htmlspecialchars() not applied here — this is an internal staff
                 * view only, not exposed to the public. Staff are trusted users.
                 */
                echo $r['purpose'];
                ?>
              </td>
            </tr>
            <?php if ($r['staff_notes']): ?>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Staff Notes:</td>
              <td style="padding:5px 0;"><?= htmlspecialchars($r['staff_notes']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($r['processed_by_name']): ?>
            <tr>
              <td style="color:#888;padding:5px 0;vertical-align:top;">Processed By:</td>
              <td style="padding:5px 0;"><?= htmlspecialchars($r['processed_by_name']) ?></td>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>

      <!-- Update form -->
      <div class="panel">
        <div class="panel-head">Update Status</div>
        <div class="panel-body">
          <form method="POST" action="/staff/view_request.php?id=<?= (int)$r['id'] ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                  <?php foreach (['pending','processing','ready','released'] as $st): ?>
                  <option value="<?= $st ?>" <?= $r['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="release_date">Release Date</label>
                <input type="date" id="release_date" name="release_date" class="form-control"
                       value="<?= htmlspecialchars($r['release_date'] ?? '') ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="staff_notes">Staff Notes</label>
              <textarea id="staff_notes" name="staff_notes" class="form-control" rows="3"
                        placeholder="e.g. Please bring your OR #2025-XXXXX when claiming."><?= htmlspecialchars($r['staff_notes'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/staff/requests.php" class="btn btn-secondary" style="margin-left:8px;">Back to List</a>
          </form>
        </div>
      </div>

    </div>

    <!-- Applicant info sidebar -->
    <div class="side-col">
      <div class="side-card">
        <h4>Applicant Information</h4>
        <table style="font-size:12.5px;width:100%;border-collapse:collapse;">
          <tr>
            <td style="color:#888;padding:3px 0;width:90px;">Name:</td>
            <td><strong><?= htmlspecialchars($r['applicant_name']) ?></strong></td>
          </tr>
          <?php if ($r['student_id_no']): ?>
          <tr>
            <td style="color:#888;padding:3px 0;">Student ID:</td>
            <td><?= htmlspecialchars($r['student_id_no']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if ($r['email']): ?>
          <tr>
            <td style="color:#888;padding:3px 0;">Email:</td>
            <td style="word-break:break-all;"><?= htmlspecialchars($r['email']) ?></td>
          </tr>
          <?php endif; ?>
          <?php if ($r['contact']): ?>
          <tr>
            <td style="color:#888;padding:3px 0;">Contact:</td>
            <td><?= htmlspecialchars($r['contact']) ?></td>
          </tr>
          <?php endif; ?>
        </table>
        <?php if ($r['student_id_no']): ?>
        <div style="margin-top:10px;">
          <a href="/staff/search.php?q=<?= urlencode($r['student_id_no']) ?>" class="btn btn-secondary btn-sm">
            Search Student Record
          </a>
        </div>
        <?php endif; ?>
      </div>

      <div class="side-card">
        <h4>Status History</h4>
        <p style="font-size:12px;color:#888;">
          Submitted: <?= date('M j, Y', strtotime($r['requested_at'])) ?>
          <?php if ($r['processed_by_name']): ?>
          <br>Last updated by: <?= htmlspecialchars($r['processed_by_name']) ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
