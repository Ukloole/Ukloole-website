<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$slug = trim($_GET['slug'] ?? '');
if(!$slug) { header('Location: /blog.php'); exit; }
$stmt = db()->prepare("SELECT * FROM blog_posts WHERE slug=? AND published=1");
$stmt->execute([$slug]);
$post = $stmt->fetch();
if(!$post) { header('HTTP/1.0 404 Not Found'); include __DIR__.'/404.php'; exit; }
$pageTitle = $post['title'];
$metaDesc  = $post['excerpt'] ?? '';
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-section" style="min-height:40vh;background-image:url('/assets/images/pixabay.jpg');background-size:cover;background-position:center;background-repeat:no-repeat;"><div class="hero-overlay"></div>
<div class="container d-flex align-items-center" style="min-height:40vh">
<div class="hero-content text-center w-100" style="padding-top:100px;padding-bottom:3rem;">
<?php if($post['category']): ?><span class="badge-premium mb-3"><?= h($post['category']) ?></span><?php endif; ?>
<h1 class="hero-title" style="font-size:clamp(1.6rem,3.5vw,2.5rem)"><?= h($post['title']) ?></h1>
<p style="color:rgba(255,255,255,.7);margin-top:12px"><?= date('M j, Y',strtotime($post['created_at'])) ?> · By <?= h($post['author']) ?></p>
</div></div></section>

<section class="py-5"><div class="container"><div class="row justify-content-center"><div class="col-lg-8">
<?php if($post['image']): ?><img src="<?= h($post['image']) ?>" alt="" style="width:100%;max-height:400px;object-fit:cover;border-radius:20px;margin-bottom:32px"><?php endif; ?>
<div style="font-size:1.1rem;line-height:1.85;color:#334155"><?= $post['content'] ?></div>
<hr class="my-5">
<a href="/blog.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Blog</a>
</div></div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
