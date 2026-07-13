<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('quotes');
$db = db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])){
        $db->prepare("DELETE FROM quotes WHERE id=?")->execute([(int)$_POST['delete_id']]);
    } elseif(!empty($_POST['status_id'])){
        $db->prepare("UPDATE quotes SET status=? WHERE id=?")->execute([trim($_POST['new_status']),(int)$_POST['status_id']]);
    }
    redirect('/admin/quotes.php');
}

$quotes = $db->query("SELECT * FROM quotes ORDER BY created_at DESC")->fetchAll();
adminHeader('Quote Requests','quotes');
?>

<!-- Message Modal -->
<div class="modal fade" id="msgModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
      <div class="modal-header" style="border-bottom:1px solid #f1f5f9;padding:1.25rem 1.5rem">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="modalName">—</h5>
          <small class="text-muted" id="modalMeta"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:1.5rem">
        <div id="modalDetails" style="margin-bottom:1rem;font-size:.88rem;color:#64748b;display:flex;gap:1.5rem;flex-wrap:wrap">
          <span><i class="bi bi-building me-1"></i><span id="modalCompany"></span></span>
          <span><i class="bi bi-gear me-1"></i><span id="modalService"></span></span>
        </div>
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
<div class="d-flex align-items-center justify-content-between p-3 border-bottom">
  <h6 class="fw-bold mb-0">Quote Requests (<?= count($quotes) ?>)</h6>
</div>
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Company</th><th>Service</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($quotes)): ?>
  <tr><td colspan="8" class="text-center py-4 text-muted">No quotes yet</td></tr>
<?php else: foreach($quotes as $q): ?>
<tr>
  <td><?= $q['id'] ?></td>
  <td>
    <a href="#" class="text-decoration-none read-msg fw-semibold"
       data-name="<?= h($q['name']) ?>"
       data-message="<?= h($q['message'] ?? '') ?>"
       data-company="<?= h($q['company'] ?? '—') ?>"
       data-service="<?= h($q['service'] ?? '—') ?>"
       data-meta="<?= date('M j, Y g:ia', strtotime($q['created_at'])) ?>"
       data-email="<?= h($q['email']) ?>">
      <?= h($q['name']) ?>
    </a>
    <br><small class="text-muted"><?= h(substr($q['message']??'',0,60)) ?>… <span class="text-primary read-more-btn" style="font-size:.78rem;cursor:pointer">read more</span></small>
  </td>
  <td><a href="mailto:<?= h($q['email']) ?>"><?= h($q['email']) ?></a></td>
  <td><?= h($q['company']??'—') ?></td>
  <td style="font-size:.88rem"><?= h($q['service']??'—') ?></td>
  <td>
    <form method="post" class="d-inline">
      <input type="hidden" name="status_id" value="<?= $q['id'] ?>">
      <select name="new_status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;font-size:.8rem">
        <?php foreach(['new','in_progress','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= $q['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </td>
  <td><?= date('M j, Y', strtotime($q['created_at'])) ?></td>
  <td>
    <form method="post" onsubmit="return confirm('Delete this quote?')">
      <input type="hidden" name="delete_id" value="<?= $q['id'] ?>">
      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
    </form>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<script>
function openQuoteModal(el) {
  var link = el.closest('td').querySelector('.read-msg');
  document.getElementById('modalName').textContent    = link.dataset.name;
  document.getElementById('modalMessage').textContent = link.dataset.message;
  document.getElementById('modalMeta').textContent    = link.dataset.meta;
  document.getElementById('modalCompany').textContent = link.dataset.company;
  document.getElementById('modalService').textContent = link.dataset.service;
  document.getElementById('modalReply').href          = 'mailto:' + link.dataset.email + '?subject=Re: Quote Request from ' + encodeURIComponent(link.dataset.name);
  new bootstrap.Modal(document.getElementById('msgModal')).show();
}
document.querySelectorAll('.read-msg').forEach(function(link) {
  link.addEventListener('click', function(e) { e.preventDefault(); openQuoteModal(this); });
});
document.querySelectorAll('.read-more-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) { e.preventDefault(); openQuoteModal(this); });
});
</script>
<?php adminFooter(); ?>