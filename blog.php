<?php
define('UKLOOLE', true);
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Blog';
$posts = db()->query("SELECT * FROM blog_posts WHERE published=1 ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<section style="background:#0f172a;color:#fff;padding:10rem 0 6rem;position:relative;overflow:hidden;border-bottom:1px solid rgba(20,184,166,.2);">
  <div style="position:absolute;top:0;right:0;width:600px;height:600px;background:rgba(99,102,241,.10);border-radius:50%;filter:blur(80px);transform:translate(30%,-50%);pointer-events:none;"></div>
  <div class="container text-center position-relative" style="z-index:2;max-width:860px;">
    <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:1.25rem;line-height:1.15;">
      Blog &amp; <span style="color:#14b8a6;">Insights</span>
    </h1>
    <p style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.75);max-width:600px;margin:0 auto;line-height:1.7;">
      Customer service tips, AI trends, and business growth strategies.
    </p>
  </div>
</section>

<section class="py-5" style="background:#f8fafc"><div class="container">
<?php if(empty($posts)): ?>
<div class="text-center py-5"><i class="bi bi-journal-text" style="font-size:4rem;color:#cbd5e1"></i><h3 class="mt-3" style="color:#64748b">No posts yet</h3><p style="color:#94a3b8">Check back soon for insights and tips from the Ukloole team.</p></div>
<?php else: ?>
<div class="row g-4">
<?php foreach($posts as $p): ?>
<div class="col-md-6 col-lg-4">
<a href="/blog-post.php?slug=<?= urlencode($p['slug']) ?>" class="text-decoration-none">
<div class="service-card h-100">
<?php if($p['image']): ?><img src="<?= h($p['image']) ?>" alt="" style="width:100%;height:180px;object-fit:cover;border-radius:16px;margin-bottom:16px"><?php endif; ?>
<?php if($p['category']): ?><span class="badge" style="background:rgba(99,102,241,.1);color:#6366f1;border-radius:50px;padding:4px 14px;font-size:.8rem;margin-bottom:10px;display:inline-block"><?= h($p['category']) ?></span><?php endif; ?>
<h4 style="color:#0f172a;font-family:Poppins,sans-serif;font-weight:700"><?= h($p['title']) ?></h4>
<?php if($p['excerpt']): ?><p style="color:#64748b;font-size:.95rem"><?= h($p['excerpt']) ?></p><?php endif; ?>
<div style="color:#94a3b8;font-size:.85rem;margin-top:auto"><?= date('M j, Y',strtotime($p['created_at'])) ?> �� <?= h($p['author']) ?></div>
</div></a></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>