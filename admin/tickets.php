<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('tickets');
$db = db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])) $db->prepare("DELETE FROM tickets WHERE id=?")->execute([(int)$_POST['delete_id']]);
    elseif(!empty($_POST['status_id'])) $db->prepare("UPDATE tickets SET status=? WHERE id=?")->execute([trim($_POST['new_status']),(int)$_POST['status_id']]);
    redirect('/admin/tickets.php');
}

$tickets = $db->query("SELECT * FROM tickets ORDER BY created_at DESC")->fetchAll();
adminHeader('Support Tickets','tickets');
?>

<!-- Message Modal -->
<div class="modal fade" id="msgModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
      <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:1.25rem 1.5rem">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="modalSubject">—</h5>
          <small class="text-muted" id="modalMeta"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.5rem">
        <div id="modalMessage" style="font-size:.97rem;line-height:1.85;color:#334155;white-space:pre-wrap;background:#f8fafc;border-radius:10px;padding:1.25rem"></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:1rem 1.5rem">
        <a id="modalReply" href="#" class="btn btn-sm btn-primary-custom"><i class="bi bi-reply me-1"></i> Reply via Email</a>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="admin-table">
<div class="p-3 border-bottom"><h6 class="fw-bold mb-0">All Tickets (<?= count($tickets) ?>)</h6></div>
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Subject</th><th>Name</th><th>Email</th><th>Priority</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($tickets)): ?>
  <tr><td colspan="8" class="text-center py-4 text-muted">No tickets yet</td></tr>
<?php else: foreach($tickets as $t): ?>
<tr>
  <td><?= $t['id'] ?></td>
  <td>
    <a href="#" class="text-decoration-none read-msg fw-semibold"
       data-subject="<?= h($t['subject']) ?>"
       data-message="<?= h($t['message']) ?>"
       data-meta="From <?= h($t['name']) ?> · <?= date('M j, Y g:ia', strtotime($t['created_at'])) ?>"
       data-email="<?= h($t['email']) ?>">
      <?= h($t['subject']) ?>
    </a>
    <br><small class="text-muted"><?= h(substr($t['message'],0,60)) ?>… <span class="text-primary read-more-btn" style="font-size:.78rem;cursor:pointer">read more</span></small>
  </td>
  <td><?= h($t['name']) ?></td>
  <td><a href="mailto:<?= h($t['email']) ?>"><?= h($t['email']) ?></a></td>
  <td>
    <span class="badge" style="background:<?= $t['priority']==='high'?'rgba(239,68,68,.1);color:#dc2626':($t['priority']==='low'?'rgba(100,116,139,.1);color:#64748b':'rgba(245,158,11,.1);color:#b45309') ?>;border-radius:50px;padding:3px 10px;font-size:.78rem">
      <?= ucfirst($t['priority']) ?>
    </span>
  </td>
  <td>
    <form method="post" class="d-inline">
      <input type="hidden" name="status_id" value="<?= $t['id'] ?>">
      <select name="new_status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;font-size:.8rem">
        <?php foreach(['open','in_progress','resolved','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= $t['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </td>
  <td><?= date('M j, Y', strtotime($t['created_at'])) ?></td>
  <td>
    <form method="post" onsubmit="return confirm('Delete?')">
      <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
    </form>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<script>
function openTicketModal(el) {
  var link = el.closest('td').querySelector('.read-msg');
  document.getElementById('modalSubject').textContent = link.dataset.subject;
  document.getElementById('modalMessage').textContent = link.dataset.message;
  document.getElementById('modalMeta').textContent    = link.dataset.meta;
  document.getElementById('modalReply').href          = 'mailto:' + link.dataset.email + '?subject=Re: ' + encodeURIComponent(link.dataset.subject);
  new bootstrap.Modal(document.getElementById('msgModal')).show();
}
document.querySelectorAll('.read-msg').forEach(function(link) {
  link.addEventListener('click', function(e) { e.preventDefault(); openTicketModal(this); });
});
document.querySelectorAll('.read-more-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) { e.preventDefault(); openTicketModal(this); });
});
</script>
<?php adminFooter(); ?>