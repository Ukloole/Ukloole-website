<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('jobs');
$db = db();

$edit = null;
if(!empty($_GET['edit'])) { $s=$db->prepare("SELECT * FROM jobs WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])) $db->prepare("DELETE FROM jobs WHERE id=?")->execute([(int)$_POST['delete_id']]);
    elseif(!empty($_POST['save'])){
        $d=[trim($_POST['title']),trim($_POST['department']??''),trim($_POST['location']??'Remote'),trim($_POST['type']??'remote'),trim($_POST['description']??''),trim($_POST['requirements']??''),trim($_POST['salary_range']??''),!empty($_POST['is_active'])?1:0];
        if(!empty($_POST['id'])) $db->prepare("UPDATE jobs SET title=?,department=?,location=?,type=?,description=?,requirements=?,salary_range=?,is_active=? WHERE id=?")->execute([...$d,(int)$_POST['id']]);
        else $db->prepare("INSERT INTO jobs (title,department,location,type,description,requirements,salary_range,is_active) VALUES (?,?,?,?,?,?,?,?)")->execute($d);
    }
    redirect('/admin/jobs.php');
}

$rows = $db->query("SELECT * FROM jobs ORDER BY created_at DESC")->fetchAll();
adminHeader('Jobs','jobs');
?>
<div class="row g-4">
<div class="col-lg-7">
<div class="admin-table">
<div class="p-3 border-bottom"><h6 class="fw-bold mb-0">Job Listings (<?= count($rows) ?>)</h6></div>
<table class="table table-hover mb-0">
<thead><tr><th>Title</th><th>Type</th><th>Location</th><th>Status</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php $tl=['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','remote'=>'Remote'];
if(empty($rows)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No jobs yet</td></tr>
<?php else: foreach($rows as $r): ?>
<tr>
<td><strong><?= h($r['title']) ?></strong><?php if($r['department']): ?><br><small class="text-muted"><?= h($r['department']) ?></small><?php endif; ?></td>
<td><?= $tl[$r['type']]??$r['type'] ?></td>
<td><?= h($r['location']??'Remote') ?></td>
<td><span class="badge-status badge-<?= $r['is_active']?'active':'closed' ?>"><?= $r['is_active']?'Active':'Closed' ?></span></td>
<td><?= date('M j, Y',strtotime($r['created_at'])) ?></td>
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
<div class="col-lg-5">
<div class="stat-card">
<h6 class="fw-bold mb-3"><?= $edit?'Edit Job':'Post a Job' ?></h6>
<form method="post" onsubmit="rteSync('description');rteSync('requirements');">
<?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<div class="mb-2"><label class="form-label fw-600">Job Title *</label><input type="text" name="title" class="form-control" value="<?= h($edit['title']??'') ?>" required></div>
<div class="mb-2"><label class="form-label fw-600">Department</label><input type="text" name="department" class="form-control" value="<?= h($edit['department']??'') ?>"></div>
<div class="mb-2"><label class="form-label fw-600">Location</label><input type="text" name="location" class="form-control" value="<?= h($edit['location']??'Remote') ?>"></div>
<div class="mb-2"><label class="form-label fw-600">Type</label>
<select name="type" class="form-select"><?php foreach(['remote'=>'Remote','full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($edit['type']??'remote')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
<div class="mb-2"><label class="form-label fw-600">Salary Range</label><input type="text" name="salary_range" class="form-control" placeholder="e.g. $500-$800/mo" value="<?= h($edit['salary_range']??'') ?>"></div>
<div class="mb-2">
<label class="form-label fw-600">Description</label>
<div class="rte-toolbar">
<button type="button" onclick="rteCmd('description','bold')" title="Bold"><b>B</b></button>
<button type="button" onclick="rteCmd('description','italic')" title="Italic"><i>i</i></button>
<button type="button" onclick="rteCmd('description','insertUnorderedList')" title="Bullet list"><i class="bi bi-list-ul"></i></button>
</div>
<div id="rte_description" class="form-control rte-editable" contenteditable="true" oninput="rteSync('description')"><?= $edit['description'] ?? '' ?></div>
<textarea name="description" id="rte_description_input" style="display:none"></textarea>
</div>
<div class="mb-2">
<label class="form-label fw-600">Requirements</label>
<div class="rte-toolbar">
<button type="button" onclick="rteCmd('requirements','bold')" title="Bold"><b>B</b></button>
<button type="button" onclick="rteCmd('requirements','italic')" title="Italic"><i>i</i></button>
<button type="button" onclick="rteCmd('requirements','insertUnorderedList')" title="Bullet list"><i class="bi bi-list-ul"></i></button>
</div>
<div id="rte_requirements" class="form-control rte-editable" contenteditable="true" oninput="rteSync('requirements')"><?= $edit['requirements'] ?? '' ?></div>
<textarea name="requirements" id="rte_requirements_input" style="display:none"></textarea>
</div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" id="active" <?= (($edit['is_active']??1))?'checked':'' ?>><label class="form-check-label" for="active">Active (visible on site)</label></div>
<button type="submit" name="save" value="1" class="btn btn-primary-custom w-100"><?= $edit?'Update Job':'Post Job' ?></button>
<?php if($edit): ?><a href="/admin/jobs.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a><?php endif; ?>
</form></div></div></div>
<style>
.rte-toolbar{display:flex;gap:4px;margin-bottom:4px}
.rte-toolbar button{width:32px;height:32px;border:1px solid #dee2e6;background:#fff;border-radius:6px;cursor:pointer;font-size:.85rem}
.rte-toolbar button:hover{background:#f1f5f9}
.rte-editable{min-height:90px;overflow-y:auto}
.rte-editable:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.15)}
</style>
<script>
function rteCmd(field, cmd) {
  var el = document.getElementById('rte_' + field);
  el.focus();
  document.execCommand(cmd, false, null);
  rteSync(field);
}
function rteSync(field) {
  document.getElementById('rte_' + field + '_input').value = document.getElementById('rte_' + field).innerHTML;
}
</script>
<?php adminFooter(); ?>
