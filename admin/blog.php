<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('blog');
$db = db();

$edit = null;
if(!empty($_GET['edit'])) { $s=$db->prepare("SELECT * FROM blog_posts WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }

if($_SERVER['REQUEST_METHOD']==='POST'){
    try {
    if(!empty($_POST['delete_id'])){
        $db->prepare("DELETE FROM blog_posts WHERE id=?")->execute([(int)$_POST['delete_id']]);
    } elseif(!empty($_POST['save'])){
        $title   = trim($_POST['title']);
        $slug    = strtolower(preg_replace('/[^a-z0-9]+/','-',trim($title)));
        $excerpt = trim($_POST['excerpt']??'');
        $content = trim($_POST['content']??'');
        $category= trim($_POST['category']??'');
        $author  = trim($_POST['author']??'Ukloole');
        $pub     = !empty($_POST['published'])?1:0;

        // Handle image upload
        $image = null;
        if(!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
            $allowed_ext = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, $allowed_ext)){
                $filename  = 'blog_' . time() . '_' . substr(md5(rand()), 0, 8) . '.' . $ext;
                $uploadDir = dirname(__DIR__) . '/uploads/blog/';
                if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                $image = '/uploads/blog/' . $filename;
            }
        }

        if(!empty($_POST['id'])){
            if($image){
                $db->prepare("UPDATE blog_posts SET title=?,slug=?,excerpt=?,content=?,category=?,author=?,published=?,image=? WHERE id=?")
                   ->execute([$title,$slug,$excerpt,$content,$category,$author,$pub,$image,(int)$_POST['id']]);
            } else {
                $db->prepare("UPDATE blog_posts SET title=?,slug=?,excerpt=?,content=?,category=?,author=?,published=? WHERE id=?")
                   ->execute([$title,$slug,$excerpt,$content,$category,$author,$pub,(int)$_POST['id']]);
            }
        } else {
            $db->prepare("INSERT INTO blog_posts (title,slug,excerpt,content,category,author,published,image) VALUES (?,?,?,?,?,?,?,?)")
               ->execute([$title,$slug,$excerpt,$content,$category,$author,$pub,$image]);
        }
    }
    } catch(Exception $e) {
        die('<pre style="color:red;padding:20px">ERROR: ' . $e->getMessage() . '</pre>');
    }
    redirect('/admin/blog.php');
}

$posts = $db->query("SELECT id,title,category,author,published,created_at FROM blog_posts ORDER BY created_at DESC")->fetchAll();
adminHeader('Blog Posts','blog');
?>
<div class="row g-4">
<div class="col-lg-7">
<div class="admin-table">
<div class="p-3 border-bottom"><h6 class="fw-bold mb-0">All Posts (<?= count($posts) ?>)</h6></div>
<table class="table table-hover mb-0">
<thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Published</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php if(empty($posts)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No posts yet</td></tr>
<?php else: foreach($posts as $p): ?>
<tr>
<td><strong><?= h($p['title']) ?></strong></td>
<td><?= h($p['category']??'—') ?></td>
<td><?= h($p['author']??'Ukloole') ?></td>
<td><span class="badge-status badge-<?= $p['published']?'active':'closed' ?>"><?= $p['published']?'Live':'Draft' ?></span></td>
<td><?= date('M j, Y',strtotime($p['created_at'])) ?></td>
<td class="d-flex gap-1">
<a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
<form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
<input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</form>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
<div class="col-lg-5">
<div class="stat-card">
<h6 class="fw-bold mb-3"><?= $edit?'Edit Post':'New Post' ?></h6>
<form method="post" enctype="multipart/form-data">
<?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
<div class="mb-2"><label class="form-label fw-600">Title *</label><input type="text" name="title" class="form-control" value="<?= h($edit['title']??'') ?>" required></div>
<div class="mb-2">
  <label class="form-label fw-600">Category</label>
  <select name="category" class="form-control">
    <option value="" disabled <?= empty($edit['category']) ? 'selected' : '' ?>>Select a category...</option>
    <?php foreach(['Business Tips','Remote Work','Customer Service','News & Updates','AI & Automation','Case Studies'] as $cat): ?>
    <option value="<?= h($cat) ?>" <?= (($edit['category']??'') === $cat) ? 'selected' : '' ?>><?= h($cat) ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-2"><label class="form-label fw-600">Author</label><input type="text" name="author" class="form-control" value="<?= h($edit['author']??'Ukloole') ?>"></div>
<div class="mb-2"><label class="form-label fw-600">Excerpt</label><textarea name="excerpt" class="form-control" rows="2"><?= h($edit['excerpt']??'') ?></textarea></div>
<div class="mb-2"><label class="form-label fw-600">Content (HTML allowed)</label><textarea name="content" class="form-control" rows="6"><?= h($edit['content']??'') ?></textarea></div>
<div class="mb-2">
  <label class="form-label fw-600">Cover Image</label>
  <?php if(!empty($edit['image'])): ?>
  <div class="mb-2">
    <img src="<?= h($edit['image']) ?>" alt="Current cover" style="width:100%;height:140px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;">
    <small class="text-muted d-block mt-1">Upload a new image below to replace this one.</small>
  </div>
  <?php endif; ?>
  <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
  <small class="text-muted">JPG, PNG, WebP or GIF. Shown as the blog card cover image.</small>
</div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="published" id="pub" value="1" <?= !empty($edit['published'])?'checked':'' ?>><label class="form-check-label" for="pub">Publish (visible on site)</label></div>
<button type="submit" name="save" value="1" class="btn btn-primary-custom w-100"><?= $edit?'Update Post':'Publish Post' ?></button>
<?php if($edit): ?><a href="/admin/blog.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a><?php endif; ?>
</form></div></div></div>
<?php adminFooter(); ?>