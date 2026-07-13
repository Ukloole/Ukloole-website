<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('users');
$db = db();

$error = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id']) && (int)$_POST['delete_id'] !== (int)$_SESSION['admin_id']){
        $db->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_POST['delete_id']]);
    } elseif(!empty($_POST['save'])){
        $user = trim($_POST['username']);
        $pass = trim($_POST['password']??'');
        $role = in_array($_POST['role'],['admin','staff'])?$_POST['role']:'staff';
        $perms = $role === 'staff'
            ? implode(',', array_intersect((array)($_POST['permissions'] ?? []), GRANTABLE_PAGES))
            : null; // admins implicitly see everything, no need to store a list
        if(!empty($_POST['id'])){
            if($pass) $db->prepare("UPDATE users SET username=?,password=?,role=?,permissions=? WHERE id=?")->execute([$user,hashPw($pass),$role,$perms,(int)$_POST['id']]);
            else $db->prepare("UPDATE users SET username=?,role=?,permissions=? WHERE id=?")->execute([$user,$role,$perms,(int)$_POST['id']]);
        } else {
            if(!$pass) { $error='Password is required for new users.'; }
            else {
                try { $db->prepare("INSERT INTO users (username,password,role,permissions) VALUES (?,?,?,?)")->execute([$user,hashPw($pass),$role,$perms]); }
                catch(Exception $e) { $error='Username already exists.'; }
            }
        }
        if(!$error) redirect('/admin/users.php');
    }
}
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
adminHeader('Users','users');
?>
<div class="row g-4">
<div class="col-lg-8">
<div class="admin-table">
<div class="p-3 border-bottom"><h6 class="fw-bold mb-0">Admin Users (<?= count($users) ?>)</h6></div>
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Username</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><strong><?= h($u['username']) ?></strong><?php if($u['id']==$_SESSION['admin_id']): ?> <span class="badge bg-primary">You</span><?php endif; ?></td>
<td>
<span class="badge-status badge-<?= $u['role']==='admin'?'active':'open' ?>"><?= ucfirst($u['role']) ?></span>
<?php if($u['role']==='staff' && !empty($u['permissions'])): ?>
<br><small class="text-muted"><?= h(str_replace(',', ', ', $u['permissions'])) ?></small>
<?php elseif($u['role']==='staff'): ?>
<br><small class="text-muted">No pages assigned</small>
<?php endif; ?>
</td>
<td><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
<td class="d-flex gap-1">
<a href="?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
<?php if($u['id'] != $_SESSION['admin_id']): ?>
<form method="post" class="d-inline" onsubmit="return confirm('Delete this user?')">
<input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</form>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
<div class="col-lg-4">
<?php $editU=null; if(!empty($_GET['edit'])){ $s=$db->prepare("SELECT * FROM users WHERE id=?"); $s->execute([(int)$_GET['edit']]); $editU=$s->fetch(); } ?>
<div class="stat-card">
<?php if($error): ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php endif; ?>
<h6 class="fw-bold mb-3"><?= $editU?'Edit User':'New User' ?></h6>
<form method="post">
<?php if($editU): ?><input type="hidden" name="id" value="<?= $editU['id'] ?>"><?php endif; ?>
<div class="mb-2"><label class="form-label fw-600">Username *</label><input type="text" name="username" class="form-control" value="<?= h($editU['username']??'') ?>" required></div>
<div class="mb-2"><label class="form-label fw-600">Password <?= $editU?'(leave blank to keep)':' *' ?></label><input type="password" name="password" class="form-control" <?= $editU?'':'required' ?> placeholder="••••••••"></div>
<div class="mb-3"><label class="form-label fw-600">Role</label>
<select name="role" id="roleSelect" class="form-select" onchange="document.getElementById('permsBlock').style.display=this.value==='staff'?'block':'none'"><option value="staff" <?= (($editU['role']??'staff')==='staff'?'selected':'') ?>>Staff</option><option value="admin" <?= (($editU['role']??'')==='admin'?'selected':'') ?>>Admin</option></select>
<small class="text-muted">Admins can see and manage everything. Staff only see the pages you tick below.</small>
</div>
<?php
$pageLabels = ['jobs'=>'Jobs','applications'=>'Applications','email_templates'=>'Email Templates','quotes'=>'Quotes','tickets'=>'Tickets','subscribers'=>'Subscribers','testimonials'=>'Testimonials','blog'=>'Blog Posts','tools'=>'Tools'];
$editPerms = array_filter(array_map('trim', explode(',', $editU['permissions'] ?? '')));
?>
<div class="mb-3" id="permsBlock" style="display:<?= (($editU['role']??'staff')==='staff')?'block':'none' ?>">
<label class="form-label fw-600">Pages this user can access</label>
<div class="d-flex flex-column gap-1">
<?php foreach($pageLabels as $key=>$label): ?>
<div class="form-check">
<input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $key ?>" id="perm_<?= $key ?>" <?= in_array($key,$editPerms,true)?'checked':'' ?>>
<label class="form-check-label" for="perm_<?= $key ?>"><?= $label ?></label>
</div>
<?php endforeach; ?>
</div>
</div>
<button type="submit" name="save" value="1" class="btn btn-primary-custom w-100"><?= $editU?'Update User':'Create User' ?></button>
<?php if($editU): ?><a href="/admin/users.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a><?php endif; ?>
</form></div></div></div>
<?php adminFooter(); ?>
