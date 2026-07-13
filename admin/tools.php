<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('tools');
$db = db();

// Safe migration — add new columns if missing
foreach(['pricing varchar(255)','best_for varchar(255)','rating decimal(2,1) DEFAULT 4.5','icon varchar(500)'] as $col) {
  try { $db->exec("ALTER TABLE tools ADD COLUMN $col"); } catch(Exception $e) {}
}

// Fixed list of business tool categories — keeps grouping on the frontend consistent
$TOOL_CATEGORIES = [
  'CRM',
  'Design',
  'HR',
  'Payroll',
  'Marketing',
  'Email Marketing',
  'Accounting & Finance',
  'Project Management',
  'Communication & Collaboration',
  'Customer Support',
  'E-commerce',
  'Productivity',
  'Analytics',
  'Sales',
  'Social Media',
  'Website & Hosting',
  'All-in-one Suite',
  'Other',
];

$edit = null;
if(!empty($_GET['edit'])) {
  $s = $db->prepare("SELECT * FROM tools WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $edit = $s->fetch();
}

if($_SERVER['REQUEST_METHOD']==='POST') {
  if(!empty($_POST['delete_id'])) {
    $db->prepare("DELETE FROM tools WHERE id=?")->execute([(int)$_POST['delete_id']]);
  } elseif(!empty($_POST['save'])) {
    $d = [
      trim($_POST['name']),
      trim($_POST['description'] ?? ''),
      trim($_POST['url']),
      trim($_POST['category'] ?? ''),
      trim($_POST['pricing'] ?? ''),
      trim($_POST['best_for'] ?? ''),
      !empty($_POST['rating']) ? (float)$_POST['rating'] : 4.5,
      trim($_POST['icon'] ?? ''),
      !empty($_POST['featured']) ? 1 : 0,
    ];
    if(!empty($_POST['id'])) {
      $db->prepare("UPDATE tools SET name=?,description=?,url=?,category=?,pricing=?,best_for=?,rating=?,icon=?,featured=? WHERE id=?")
         ->execute([...$d, (int)$_POST['id']]);
    } else {
      $db->prepare("INSERT INTO tools (name,description,url,category,pricing,best_for,rating,icon,featured) VALUES (?,?,?,?,?,?,?,?,?)")
         ->execute($d);
    }
  }
  redirect('/admin/tools.php');
}

$rows = $db->query("SELECT * FROM tools ORDER BY category ASC, featured DESC, name ASC")->fetchAll();
adminHeader('Admin Tools', 'tools');
?>

<div class="row g-4">

  <!-- TABLE -->
  <div class="col-lg-8">
    <div class="admin-table">
      <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Tools Directory (<?= count($rows) ?>)</h6>
      </div>
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Pricing</th>
            <th>Rating</th>
            <th>Featured</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($rows)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">No tools yet — add your first one →</td></tr>
          <?php else: foreach($rows as $r): ?>
          <tr>
            <td>
              <strong><?= h($r['name']) ?></strong><br>
              <small class="text-muted"><?= h(substr($r['description'] ?? '', 0, 55)) ?><?= strlen($r['description'] ?? '') > 55 ? '…' : '' ?></small>
            </td>
            <td><span class="badge" style="background:#f1f5f9;color:#475569;"><?= h($r['category'] ?? '—') ?></span></td>
            <td style="font-size:.85rem;color:#64748b;"><?= h($r['pricing'] ?? '—') ?></td>
            <td>
              <?php if(!empty($r['rating'])): ?>
              <span style="color:#f59e0b;font-size:.85rem;">★</span>
              <span style="font-size:.85rem;font-weight:600;"><?= h($r['rating']) ?></span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= $r['featured'] ? '⭐' : '—' ?></td>
            <td class="d-flex gap-1">
              <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this tool?')">
                <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- FORM -->
  <div class="col-lg-4">
    <div class="stat-card">
      <h6 class="fw-bold mb-3"><?= $edit ? 'Edit Tool' : 'Add New Tool' ?></h6>
      <form method="post">
        <?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="mb-2">
          <label class="form-label fw-semibold">Tool Name *</label>
          <input type="text" name="name" class="form-control" value="<?= h($edit['name'] ?? '') ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">URL *</label>
          <input type="url" name="url" class="form-control" placeholder="https://" value="<?= h($edit['url'] ?? '') ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Category</label>
          <select name="category" class="form-control" required>
            <option value="" disabled <?= empty($edit['category']) ? 'selected' : '' ?>>Select a category…</option>
            <?php
              $legacyCat = $edit['category'] ?? '';
              if($legacyCat && !in_array($legacyCat, $TOOL_CATEGORIES, true)):
            ?>
            <option value="<?= h($legacyCat) ?>" selected><?= h($legacyCat) ?> (legacy — please update)</option>
            <?php endif; ?>
            <?php foreach($TOOL_CATEGORIES as $cat): ?>
            <option value="<?= h($cat) ?>" <?= ($legacyCat === $cat) ? 'selected' : '' ?>><?= h($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Tools with the same category are grouped together on the page.</small>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Pricing</label>
          <input type="text" name="pricing" class="form-control" placeholder="e.g. Free / ₦4,500/month" value="<?= h($edit['pricing'] ?? '') ?>">
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Best For</label>
          <input type="text" name="best_for" class="form-control" placeholder="e.g. Growing sales teams" value="<?= h($edit['best_for'] ?? '') ?>">
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Rating (out of 5)</label>
          <input type="number" name="rating" class="form-control" min="1" max="5" step="0.1" placeholder="4.5" value="<?= h($edit['rating'] ?? '4.5') ?>">
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Icon</label>
          <input type="text" name="icon" class="form-control" placeholder="Image URL or paste SVG code" value="<?= h($edit['icon'] ?? '') ?>">
          <small class="text-muted">Paste an image URL (https://...) or raw SVG code. Leave blank to show the tool's first letter.</small>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Short description of the tool..."><?= h($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="featured" id="feat" <?= !empty($edit['featured']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="feat">⭐ Mark as <strong>Ukloole Pick</strong></label>
        </div>

        <button type="submit" name="save" value="1" class="btn btn-primary-custom w-100"><?= $edit ? 'Update Tool' : 'Add Tool' ?></button>
        <?php if($edit): ?>
        <a href="/admin/tools.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

</div>
<?php adminFooter(); ?>