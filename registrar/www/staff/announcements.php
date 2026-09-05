<?php
/**
 * staff/announcements.php — Manage public announcements
 * CRUD: list, create, toggle publish, delete
 */
session_name('NMCSTAFFSESSID');
session_start();

require_once '../includes/auth.php';
require_staff();
require_once '../includes/db.php';

$page_title = 'Announcements';
$active_nav = 'announcements';

$msg = '';
$err = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body']  ?? '');
        if (!$title || !$body) {
            $err = 'Title and body are required.';
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO announcements (title, body, posted_by, is_published) VALUES (?, ?, ?, 1)'
            );
            $stmt->bind_param('ssi', $title, $body, $_SESSION['staff_id']);
            $stmt->execute();
            $stmt->close();
            $msg = 'Announcement posted.';
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->query("UPDATE announcements SET is_published = NOT is_published WHERE id = $id");
        $msg = 'Announcement updated.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM announcements WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $msg = 'Announcement deleted.';
    }
}

$announcements = $conn->query(
    'SELECT a.*, s.full_name FROM announcements a
     LEFT JOIN nmc_staff s ON a.posted_by = s.id
     ORDER BY a.created_at DESC'
)->fetch_all(MYSQLI_ASSOC);

require_once 'header.php';
?>

<div class="page-wrap">
  <div class="page-title" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
    <div>
      <h2>Announcements</h2>
      <p>Manage public announcements displayed on the portal home page.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('new-ann').style.display='block';this.style.display='none'">
      + New Announcement
    </button>
  </div>

  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div id="new-ann" style="display:none;" class="panel">
    <div class="panel-head">New Announcement</div>
    <div class="panel-body">
      <form method="POST" action="/staff/announcements.php">
        <input type="hidden" name="action" value="create">
        <div class="form-group">
          <label for="atitle">Title <span style="color:red">*</span></label>
          <input type="text" id="atitle" name="title" class="form-control" placeholder="e.g. Office Schedule Change">
        </div>
        <div class="form-group">
          <label for="abody">Body <span style="color:red">*</span></label>
          <textarea id="abody" name="body" class="form-control" rows="5"
                    placeholder="Write the announcement text here..."></textarea>
          <p class="hint">Plain text only. No HTML. Displayed verbatim on the public portal.</p>
        </div>
        <button type="submit" class="btn btn-primary">Post Announcement</button>
        <button type="button" class="btn btn-secondary" style="margin-left:8px;"
                onclick="document.getElementById('new-ann').style.display='none';document.querySelector('.btn.btn-primary[onclick]').style.display=''">
          Cancel
        </button>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">All Announcements — <?= count($announcements) ?> total</div>
    <div class="panel-body" style="padding:0;">
      <?php if (empty($announcements)): ?>
      <div style="padding:30px;text-align:center;color:#888;">No announcements yet.</div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:40%">Title</th>
            <th>Posted by</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($announcements as $a): ?>
          <tr>
            <td>
              <strong style="font-size:13.5px;"><?= htmlspecialchars($a['title']) ?></strong>
              <div style="font-size:12px;color:#888;margin-top:2px;">
                <?= htmlspecialchars(mb_strimwidth($a['body'], 0, 80, '...')) ?>
              </div>
            </td>
            <td style="font-size:12.5px;"><?= htmlspecialchars($a['full_name'] ?? '—') ?></td>
            <td style="font-size:12px;"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            <td>
              <?php if ($a['is_published']): ?>
              <span style="color:#1a7a4a;font-weight:600;font-size:12px;">Published</span>
              <?php else: ?>
              <span style="color:#888;font-size:12px;">Hidden</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" action="/staff/announcements.php" style="display:inline;">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button class="btn btn-secondary btn-sm" type="submit">
                  <?= $a['is_published'] ? 'Hide' : 'Publish' ?>
                </button>
              </form>
              <form method="POST" action="/staff/announcements.php" style="display:inline;margin-left:4px;"
                    onsubmit="return confirm('Delete this announcement?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button class="btn btn-sm" style="background:#c0392b;color:#fff;border:none;" type="submit">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
