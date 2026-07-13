<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('subscribers');
$db = db();

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])){
        $db->prepare("DELETE FROM subscribers WHERE id=?")->execute([(int)$_POST['delete_id']]);
    } elseif(!empty($_POST['toggle_id'])){
        $sub = $db->prepare("SELECT status FROM subscribers WHERE id=?");
        $sub->execute([(int)$_POST['toggle_id']]);
        $cur = $sub->fetchColumn();
        $new = $cur==='active' ? 'unsubscribed' : 'active';
        $db->prepare("UPDATE subscribers SET status=? WHERE id=?")->execute([$new,(int)$_POST['toggle_id']]);
    }
    redirect('/admin/subscribers.php');
}

// CSV Export
if(isset($_GET['export'])){
    $rows = $db->query("SELECT email,status,created_at FROM subscribers ORDER BY created_at DESC")->fetchAll();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ukloole-subscribers-'.date('Y-m-d').'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Email','Status','Subscribed Date']);
    foreach($rows as $r) fputcsv($out,[$r['email'],$r['status'],date('Y-m-d H:i:s',strtotime($r['created_at']))]);
    fclose($out);
    exit;
}

$subs = $db->query("SELECT * FROM subscribers ORDER BY created_at DESC")->fetchAll();
$active = count(array_filter($subs, fn($s) => $s['status']==='active'));
adminHeader('Subscribers','subscribers');
?>
<div class="admin-table">
<div class="d-flex align-items-center justify-content-between p-3 border-bottom">
<h6 class="fw-bold mb-0">Subscribers (<?= count($subs) ?> total · <?= $active ?> active)</h6>
<a href="?export=1" class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i>Export CSV</a>
</div>
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Email</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($subs)): ?>
<tr><td colspan="5" class="text-center py-4 text-muted">No subscribers yet</td></tr>
<?php else: foreach($subs as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><a href="mailto:<?= h($s['email']) ?>"><?= h($s['email']) ?></a></td>
<td><span class="badge-status badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
<td><?= date('M j, Y',strtotime($s['created_at'])) ?></td>
<td class="d-flex gap-1">
<form method="post" class="d-inline">
<input type="hidden" name="toggle_id" value="<?= $s['id'] ?>">
<button class="btn btn-sm btn-outline-secondary" title="Toggle status"><i class="bi bi-toggle-on"></i></button>
</form>
<form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
<input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</form>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<?php adminFooter(); ?>
