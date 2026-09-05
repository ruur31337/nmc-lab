<?php
/**
 * staff/requests.php — All document requests with filtering
 *
 * @author   rdelacruz
 * @modified 2025-01-15
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

$page_title = 'Document Requests';
$active_nav = 'requests';

$filter_status = $_GET['status'] ?? '';
$valid_statuses = ['pending', 'processing', 'ready', 'released'];

$where  = '';
$params = [];
$types  = '';
if (in_array($filter_status, $valid_statuses)) {
    $where = 'WHERE status = ?';
    $params[] = $filter_status;
    $types    = 's';
}

// v2 schema: no student FK — applicant info stored directly in document_requests
$sql = "SELECT * FROM document_requests
        $where
        ORDER BY
          FIELD(status,'pending','processing','ready','released'),
          requested_at ASC";

if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $requests = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

require_once 'header.php';
?>

<div class="page-wrap">
  <div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-start;">
    <div>
      <h2>Document Requests</h2>
      <p>All requests &mdash; <?= count($requests) ?> record<?= count($requests) !== 1 ? 's' : '' ?></p>
    </div>
  </div>

  <!-- Filter bar -->
  <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    <a href="/staff/requests.php"
       class="btn <?= $filter_status === '' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
    <?php foreach (['pending','processing','ready','released'] as $st): ?>
    <a href="/staff/requests.php?status=<?= $st ?>"
       class="btn <?= $filter_status === $st ? 'btn-primary' : 'btn-secondary' ?>"><?= ucfirst($st) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="panel">
    <div class="panel-body" style="padding:0;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Applicant</th>
              <th>Student ID</th>
              <th>Document</th>
              <th>Copies</th>
              <th>Preferred Date</th>
              <th>Status</th>
              <th>Submitted</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($requests)): ?>
            <tr><td colspan="9" class="text-center text-muted" style="padding:20px;">No requests found.</td></tr>
            <?php else: ?>
            <?php foreach ($requests as $r): ?>
            <tr>
              <td><span class="ref-no"><?= htmlspecialchars($r['tracking_code']) ?></span></td>
              <td>
                <strong><?= htmlspecialchars($r['applicant_name']) ?></strong>
                <?php if ($r['email']): ?>
                <br><span style="font-size:11px;color:#888;"><?= htmlspecialchars($r['email']) ?></span>
                <?php endif; ?>
              </td>
              <td style="font-size:12.5px;"><?= htmlspecialchars($r['student_id_no'] ?: '—') ?></td>
              <td><?= htmlspecialchars($r['doc_type']) ?></td>
              <td style="text-align:center;"><?= (int)$r['copies'] ?></td>
              <td style="font-size:12px;">
                <?= $r['release_date'] ? date('M j, Y', strtotime($r['release_date'])) : '<span style="color:#aaa;">—</span>' ?>
              </td>
              <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
              <td style="font-size:12px;white-space:nowrap;"><?= date('M j, Y', strtotime($r['requested_at'])) ?></td>
              <td><a href="/staff/view_request.php?id=<?= (int)$r['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
