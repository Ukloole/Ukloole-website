<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('testimonials');
$db = db();

$edit = null;
if(!empty($_GET['edit'])) { $stmt=$db->prepare("SELECT * FROM testimonials WHERE id=?"); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch(); }

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])){
        $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_POST['delete_id']]);
    } elseif(!empty($_POST['save'])){
        $d=[trim($_POST['client_name']),trim($_POST['company_name']??''),trim($_POST['testimonial']),(int)($_POST['rating']??5),!empty($_POST['published'])?1:0];
        if(!empty($_POST['id'])){
            $db->prepare("UPDATE testimonials SET client_name=?,company_name=?,testimonial=?,rating=?,published=? WHERE id=?")->execute([...$d,(int)$_POST['id']]);
        } else {
            $db->prepare("INSERT INTO testimonials (client_name,company_name,testimonial,rating,published) VALUES (?,?,?,?,?)")->execute($d);
        }
    }
    redirect('/admin/testimonials.php');
}

$rows = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
adminHeader('Testimonials','testimonials');
?>
<div class="row g-4">
<div class="col-lg-8">
<div class="admin-table">
<div class="p-3 border-bottom"><h6 class="fw-bold mb-0">All Testimonials (<?= count($rows) ?>)</h6></div>
<table class="table table-hover mb-0">
<thead><tr><th>Client</th><th>Company</th><th>Rating</th><th>Published</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($rows)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No testimonials yet</td></tr>
<?php else: foreach($rows as $r): ?>
<tr>
<td><strong><?= h($r['client_name']) ?></strong><br><small class="text-muted"><?= h(substr($r['testimonial'],0,60)) ?>...</small></td>
<td><?= h($r['company_name']??'—') ?></td>
<td><?= str_repeat('⭐',(int)($r['rating']??5)) ?></td>
<td><span class="badge-status badge-<?= $r['published']?'active':'closed' ?>"><?= $r['published']?'Yes':'No' ?></span></td>
<td class="d-flex gap-1">
<a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
<form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
<input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</form>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<div class="col-lg-4">
<div class="stat-card">
<h6 class="fw-bold mb-3"><?= $edit?'Edit Testimonial':'Add Testimonial' ?></h6>
<form method="post">
<?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<div class="mb-2"><label class="form-label fw-600">Client Name *</label><input type="text" name="client_name" class="form-control" value="<?= h($edit['client_name']??'') ?>" required></div>
<div class="mb-2"><label class="form-label fw-600">Company</label><input type="text" name="company_name" class="form-control" value="<?= h($edit['company_name']??'') ?>"></div>
<div class="mb-2"><label class="form-label fw-600">Testimonial *</label><textarea name="testimonial" class="form-control" rows="4" required><?= h($edit['testimonial']??'') ?></textarea></div>
<div class="mb-2"><label class="form-label fw-600">Rating</label>
<select name="rating" class="form-select"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= (($edit['rating']??5)==$i)?'selected':'' ?>><?= $i ?> Stars</option><?php endfor; ?></select></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="published" id="pub" <?= !empty($edit['published'])?'checked':'' ?>><label class="form-check-label" for="pub">Published (visible on site)</label></div>
<button type="submit" name="save" value="1" class="btn btn-primary-custom w-100"><?= $edit?'Update':'Add Testimonial' ?></button>
<?php if($edit): ?><a href="/admin/testimonials.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a><?php endif; ?>
</form></div></div></div>
<?php adminFooter(); ?>
