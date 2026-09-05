<?php
/**
 * staff/search.php — Student lookup by ID or name
 *
 * @author   rdelacruz
 * @modified 2025-02-14
 *
 * Updated (acruz, 2025-02-14):
 * Added input validation. OR LIKE logic updated with keyword filtering
 * as a stopgap until the query is refactored.
 *
 * TODO: refactor query when time permits (acruz, 2025-01-20)
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

$page_title = 'Student Search';
$active_nav = 'search';

$q        = trim($_GET['q'] ?? '');
$students = [];
$searched = false;
$sql_error   = '';
$blocked_msg = '';

if ($q !== '') {
    $searched = true;

    // Basic input policy — filter invalid characters.
    // (acruz, 2025-02-14) — stopgap until query refactor.
    $blocked = [' union ', ' sleep', ' benchmark',
                ' outfile', ' load_file', 'information_schema'];
    $lower_q = strtolower($q);
    $is_blocked = false;

    foreach ($blocked as $kw) {
        if (strpos($lower_q, $kw) !== false) {
            $is_blocked = true;
            break;
        }
    }

    if ($is_blocked) {
        $blocked_msg = 'Input validation policy rejected the search term.';
    } else {
        $sql = "SELECT * FROM students WHERE student_id = '$q' OR surname LIKE '%$q%' OR given_name LIKE '%$q%' ORDER BY surname ASC";

        try {
            $result = $conn->query($sql);
            $students = $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            // Suppress raw DB errors — log internally
            $sql_error = 'An error occurred while processing your search. Please try again.';
        }
    }
}

require_once 'header.php';
?>

<div class="page-wrap">
  <div class="page-title">
    <h2>Student Search</h2>
    <p>Look up a student by ID number or name to view their profile and requests.</p>
  </div>

  <div class="panel">
    <div class="panel-body">
      <form method="GET" action="/staff/search.php" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:240px;">
          <label for="q">Student ID or Name</label>
          <input type="text" id="q" name="q" class="form-control"
                 value="<?= htmlspecialchars($q) ?>"
                 placeholder="e.g. 2022-00018 or Santos"
                 autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary" style="height:38px;">Search</button>
        <?php if ($q): ?>
        <a href="/staff/search.php" class="btn btn-secondary" style="height:38px;line-height:24px;">Clear</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <?php if ($blocked_msg): ?>
  <div class="alert alert-error">
    <strong>Request blocked:</strong> <?= htmlspecialchars($blocked_msg) ?>
  </div>
  <?php endif; ?>

  <?php if ($sql_error): ?>
  <div class="alert alert-error">
    <strong>Database error:</strong> <?= htmlspecialchars($sql_error) ?>
  </div>
  <?php endif; ?>

  <?php if ($searched): ?>
  <div class="panel">
    <div class="panel-head">
      Search Results <?php if ($q): ?>for &ldquo;<?= htmlspecialchars($q) ?>&rdquo;<?php endif; ?>
      &mdash; <?= count($students) ?> found
    </div>
    <div class="panel-body" style="padding:0;">
      <?php if (empty($students)): ?>
      <div class="text-center text-muted" style="padding:30px;">No students found matching your search.</div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student ID</th>
              <th>Name</th>
              <th>Course</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
              <td><?= htmlspecialchars($s['surname'] . ', ' . $s['given_name']) ?></td>
              <td><?= htmlspecialchars($s['course']) ?></td>
              <td>
                <?php
                $sc = ['active' => '#1a7a4a', 'inactive' => '#888', 'enrolled' => '#1a5c8a', 'leave' => '#c9961a'];
                $c  = $sc[$s['status']] ?? '#555';
                ?>
                <span style="color:<?= $c ?>;font-weight:600;font-size:12px;"><?= ucfirst(htmlspecialchars($s['status'])) ?></span>
              </td>
              <td>
                <a href="/staff/search.php?q=<?= urlencode($s['student_id']) ?>&profile=1"
                   class="btn btn-secondary btn-sm">Profile</a>
              </td>
            </tr>

            <?php
            // If profile view requested for this exact student
            if (isset($_GET['profile']) && $_GET['q'] === $s['student_id']):
                $pstmt = $conn->prepare(
                    'SELECT * FROM document_requests WHERE student_id_no = ? ORDER BY requested_at DESC'
                );
                $pstmt->bind_param('s', $s['student_id']);
                $pstmt->execute();
                $dreqs = $pstmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $pstmt->close();

                // Fetch grades
                $gstmt = $conn->prepare(
                    'SELECT * FROM grades WHERE student_id = ? ORDER BY academic_year, FIELD(semester,"1st","2nd","Summer")'
                );
                $gstmt->bind_param('s', $s['student_id']);
                $gstmt->execute();
                $grades = $gstmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $gstmt->close();
            ?>
            <tr>
              <td colspan="8" style="background:#fafaf8;padding:16px 20px;">
                <h4 style="margin:0 0 12px;color:#6B0F1A;">
                  Student Profile: <?= htmlspecialchars($s['surname'] . ', ' . $s['given_name']) ?>
                </h4>
                <?php if ($dreqs): ?>
                <p style="font-size:13px;font-weight:600;margin:0 0 6px;">Document Requests (<?= count($dreqs) ?>)</p>
                <table class="data-table" style="margin-bottom:14px;">
                  <thead>
                    <tr><th>Ref No.</th><th>Document</th><th>Copies</th><th>Status</th><th>Submitted</th><th></th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($dreqs as $dr): ?>
                    <tr>
                      <td><span class="ref-no"><?= htmlspecialchars($dr['tracking_code']) ?></span></td>
                      <td><?= htmlspecialchars($dr['doc_type']) ?></td>
                      <td style="text-align:center;"><?= (int)$dr['copies'] ?></td>
                      <td><span class="badge badge-<?= $dr['status'] ?>"><?= ucfirst($dr['status']) ?></span></td>
                      <td style="font-size:12px;"><?= date('M j, Y', strtotime($dr['requested_at'])) ?></td>
                      <td><a href="/staff/view_request.php?id=<?= (int)$dr['id'] ?>" class="btn btn-secondary btn-sm">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php else: ?>
                <p style="font-size:13px;color:#888;">No document requests on file.</p>
                <?php endif; ?>

                <?php if ($grades): ?>
                <p style="font-size:13px;font-weight:600;margin:0 0 6px;">Grades (<?= count($grades) ?> subjects on file)</p>
                <table class="data-table">
                  <thead>
                    <tr><th>Subject Code</th><th>Subject Name</th><th>Units</th><th>Grade</th><th>Semester</th><th>AY</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($grades as $g): ?>
                    <tr>
                      <td><?= htmlspecialchars($g['subject_code']) ?></td>
                      <td><?= htmlspecialchars($g['subject_name']) ?></td>
                      <td style="text-align:center;"><?= htmlspecialchars($g['units']) ?></td>
                      <td class="grade-val"><?= htmlspecialchars($g['grade']) ?></td>
                      <td><?= htmlspecialchars($g['semester']) ?></td>
                      <td><?= htmlspecialchars($g['academic_year']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php else: ?>
                <p style="font-size:13px;color:#888;">No grade records on file.</p>
                <?php endif; ?>
              </td>
            </tr>
            <?php endif; ?>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>
