<?php
/**
 * staff/dashboard.php — Staff overview dashboard
 *
 * @author   rdelacruz
 * @modified 2025-01-12
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

$page_title = 'Dashboard';
$active_nav = 'dashboard';

// Counts by status
$counts = [];
$res = $conn->query('SELECT status, COUNT(*) as n FROM document_requests GROUP BY status');
while ($row = $res->fetch_assoc()) {
    $counts[$row['status']] = (int)$row['n'];
}

// Recent requests (last 10, any status) — v2: no student JOIN
$recent = $conn->query(
    'SELECT * FROM document_requests ORDER BY requested_at DESC LIMIT 10'
)->fetch_all(MYSQLI_ASSOC);

// Requests needing action (pending + processing)
$action_count = ($counts['pending'] ?? 0) + ($counts['processing'] ?? 0);

require_once 'header.php';
?>

<div class="page-wrap">
  <div class="page-title">
    <h2>Dashboard</h2>
    <p>Office of the Registrar &mdash; document processing overview</p>
  </div>

  <!-- Status summary cards -->
  <div class="stat-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px;">
    <?php
    $cards = [
      ['pending',    'Pending',          '#c9961a', '#fdf6e3'],
      ['processing', 'In Processing',    '#1a5c8a', '#e8f4fc'],
      ['ready',      'Ready for Pickup', '#1a7a4a', '#e8f9ef'],
      ['released',   'Released',         '#555',    '#f5f4f2'],
    ];
    foreach ($cards as [$st, $label, $color, $bg]): ?>
    <a href="/staff/requests.php?status=<?= $st ?>"
       style="text-decoration:none;background:<?= $bg ?>;border:1px solid <?= $color ?>22;border-radius:6px;padding:18px 16px;text-align:center;display:block;">
      <div style="font-size:32px;font-weight:700;color:<?= $color ?>;"><?= $counts[$st] ?? 0 ?></div>
      <div style="font-size:12px;color:<?= $color ?>;font-weight:600;margin-top:4px;"><?= $label ?></div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if ($action_count > 0): ?>
  <div class="alert" style="background:#fdf6e3;border:1px solid #e8c97a;color:#7a5c00;margin-bottom:16px;">
    <strong><?= $action_count ?> request<?= $action_count !== 1 ? 's' : '' ?></strong> require action (Pending or In Processing).
    <a href="/staff/requests.php?status=pending" style="color:#7a5c00;font-weight:600;">View pending &rsaquo;</a>
  </div>
  <?php endif; ?>

  <!-- Recent requests table -->
  <div class="panel">
    <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
      <span>Recent Requests</span>
      <a href="/staff/requests.php" style="font-size:12px;font-weight:normal;">View all &rsaquo;</a>
    </div>
    <div class="panel-body" style="padding:0;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Applicant</th>
              <th>Student ID</th>
              <th>Document Type</th>
              <th>Copies</th>
              <th>Status</th>
              <th>Submitted</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recent)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:20px;">No requests yet.</td></tr>
            <?php else: ?>
            <?php foreach ($recent as $r): ?>
            <tr>
              <td><span class="ref-no"><?= htmlspecialchars($r['tracking_code']) ?></span></td>
              <td>
                <strong><?= htmlspecialchars($r['applicant_name']) ?></strong>
              </td>
              <td style="font-size:12px;color:#888;"><?= htmlspecialchars($r['student_id_no'] ?: '—') ?></td>
              <td><?= htmlspecialchars($r['doc_type']) ?></td>
              <td style="text-align:center;"><?= (int)$r['copies'] ?></td>
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
