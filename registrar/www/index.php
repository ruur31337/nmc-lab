<?php
/**
 * index.php — NMC ORDIR public main page
 *
 * @author   rdelacruz
 */
require_once 'includes/db.php';

$page_title = 'NMC Registrar Portal';
$active_nav = 'home';

$q       = trim($_GET['q'] ?? '');
$request = null;

if ($q !== '') {
    $stmt = $conn->prepare(
        'SELECT * FROM document_requests WHERE tracking_code = ?'
    );
    $stmt->bind_param('s', $q);
    $stmt->execute();
    $res     = $stmt->get_result();
    $request = $res->num_rows > 0 ? $res->fetch_assoc() : null;
    $stmt->close();
}

$announcements = $conn->query(
    "SELECT a.*, s.full_name AS posted_by_name
     FROM announcements a LEFT JOIN nmc_staff s ON s.id = a.posted_by
     WHERE a.is_published = 1 ORDER BY a.created_at DESC LIMIT 4"
)->fetch_all(MYSQLI_ASSOC);

$status_steps = ['pending' => 1, 'processing' => 2, 'ready' => 3, 'released' => 4];
require_once 'includes/public_header.php';
?>
<div class="page-wrap">

  <!-- Tracking search -->
  <div class="panel" style="margin-bottom:20px;">
    <div class="panel-head" style="font-size:15px;">Track Your Document Request</div>
    <div class="panel-body">
      <p style="color:#666;font-size:13px;margin-bottom:14px;">
        Enter your <strong>Tracking Code</strong> (e.g. <code>REG-2025-0041</code>) to check the status of your request.
      </p>
      <form method="GET" action="/" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <div class="form-group" style="flex:1;min-width:240px;margin-bottom:0;">
          <label for="q">Tracking Code</label>
          <input type="text" id="q" name="q" class="form-control"
                 value="<?= htmlspecialchars($q) ?>"
                 placeholder="REG-2025-XXXX" autocomplete="off"
                 style="font-size:15px;height:42px;">
        </div>
        <button type="submit" class="btn btn-primary" style="height:42px;padding:0 28px;font-size:14px;">Track</button>
      </form>

      <?php if ($q): ?>
        <?php if ($request): ?>
        <div style="margin-top:20px;border-top:1px solid #e8e2dc;padding-top:18px;">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
            <div>
              <span class="ref-no"><?= htmlspecialchars($request['tracking_code']) ?></span>
              <span style="color:#888;font-size:13px;margin-left:8px;">&mdash; <?= htmlspecialchars($request['doc_type']) ?></span>
            </div>
            <span class="badge badge-<?= $request['status'] ?>"><?= ucfirst($request['status']) ?></span>
          </div>
          <!-- Progress -->
          <?php $step = $status_steps[$request['status']] ?? 1; ?>
          <div style="display:flex;gap:0;margin-bottom:18px;font-size:11px;text-align:center;">
            <?php foreach (['Submitted','Processing','Ready for Pickup','Released'] as $i => $label):
              $done=$i+1<$step; $cur=$i+1===$step;
              $col=$done?'#1a7a4a':($cur?'#6B0F1A':'#ccc'); ?>
            <div style="flex:1;position:relative;">
              <div style="width:26px;height:26px;border-radius:50%;background:<?=$col?>;color:#fff;line-height:26px;font-weight:700;margin:0 auto 5px;font-size:12px;"><?=$done?'&#10003;':$i+1?></div>
              <div style="color:<?=$cur?'#6B0F1A':($done?'#1a7a4a':'#aaa')?>;font-weight:<?=$cur?'700':'400'?>;"><?=$label?></div>
              <?php if($i<3):?><div style="position:absolute;top:12px;left:63%;width:74%;height:2px;background:<?=$done?'#1a7a4a':'#e0dbd5'?>;"></div><?php endif;?>
            </div>
            <?php endforeach;?>
          </div>
          <table style="font-size:13px;width:100%;">
            <tr><td style="width:150px;color:#888;padding:4px 0;">Applicant:</td><td><?= htmlspecialchars($request['applicant_name']) ?></td></tr>
            <tr><td style="color:#888;padding:4px 0;">Student ID:</td><td><?= htmlspecialchars($request['student_id_no'] ?? '—') ?></td></tr>
            <tr><td style="color:#888;padding:4px 0;">Document:</td><td><?= htmlspecialchars($request['doc_type']) ?> &times;<?= (int)$request['copies'] ?></td></tr>
            <tr><td style="color:#888;padding:4px 0;">Submitted:</td><td><?= date('F j, Y, g:i A', strtotime($request['requested_at'])) ?></td></tr>
            <?php if($request['release_date']):?><tr><td style="color:#888;padding:4px 0;">Preferred Release:</td><td><?= date('F j, Y', strtotime($request['release_date'])) ?></td></tr><?php endif;?>
            <?php if($request['staff_notes']):?><tr><td style="color:#888;padding:4px 0;vertical-align:top;">Registrar Note:</td><td style="color:#444;"><?= htmlspecialchars($request['staff_notes']) ?></td></tr><?php endif;?>
          </table>
          <?php if($request['status']==='ready'):?>
          <div class="alert alert-warning" style="margin-top:12px;margin-bottom:0;">
            &#127881; Your document is <strong>ready for pickup</strong> at <strong>Window 3</strong>, Registrar's Office (Bldg A, Room 105). Bring a valid ID and your Official Receipt.
          </div>
          <?php endif;?>
        </div>
        <?php else:?>
        <div class="alert alert-error" style="margin-top:14px;">
          No request found for <strong><?= htmlspecialchars($q) ?></strong>.
          Check your code or <a href="/forgot-code.php">retrieve your tracking code</a>.
        </div>
        <?php endif;?>
      <?php endif;?>
    </div>
  </div>

  <!-- Quick actions -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
    <a href="/apply.php" class="panel" style="text-decoration:none;text-align:center;padding:28px 16px;display:block;">
      <div style="font-size:30px;margin-bottom:8px;">&#128196;</div>
      <div style="font-weight:700;color:#6B0F1A;font-size:14px;">New Document Request</div>
      <div style="font-size:12px;color:#888;margin-top:4px;">Submit a request for TOR, COE, GMC, and other official documents.</div>
    </a>
    <a href="/forgot-code.php" class="panel" style="text-decoration:none;text-align:center;padding:28px 16px;display:block;">
      <div style="font-size:30px;margin-bottom:8px;">&#128273;</div>
      <div style="font-weight:700;color:#6B0F1A;font-size:14px;">Forgot Tracking Code?</div>
      <div style="font-size:12px;color:#888;margin-top:4px;">Retrieve your tracking code using your name and student ID number.</div>
    </a>
  </div>

  <!-- Announcements -->
  <?php if($announcements):?>
  <div class="panel">
    <div class="panel-head">Announcements</div>
    <div class="panel-body" style="padding:0;">
      <?php foreach($announcements as $i=>$ann):?>
      <div style="padding:16px 20px;<?=$i<count($announcements)-1?'border-bottom:1px solid #f0ece8;':''?>">
        <div style="font-weight:600;font-size:14px;margin-bottom:4px;"><?= htmlspecialchars($ann['title']) ?></div>
        <div style="font-size:11.5px;color:#888;margin-bottom:6px;">
          <?= htmlspecialchars($ann['posted_by_name'] ?? 'Registrar Office') ?> &mdash; <?= date('F j, Y', strtotime($ann['created_at'])) ?>
        </div>
        <div style="font-size:13px;color:#555;line-height:1.6;"><?= nl2br(htmlspecialchars($ann['body'])) ?></div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
  <?php endif;?>

</div>
<?php require_once 'includes/footer.php'; ?>
