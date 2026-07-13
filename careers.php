<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Careers';
$jobs = db()->query("SELECT * FROM jobs WHERE is_active=1 ORDER BY created_at DESC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<section style="background:#0f172a;color:#fff;padding:10rem 0 6rem;position:relative;overflow:hidden;border-bottom:1px solid rgba(20,184,166,.2);"><div style="position:absolute;top:0;right:0;width:600px;height:600px;background:rgba(99,102,241,.10);border-radius:50%;filter:blur(80px);transform:translate(30%,-50%);pointer-events:none;"></div>
<div class="container text-center position-relative" style="z-index:2;max-width:860px;">
<h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:1.25rem;line-height:1.15;">Join the <span style="color:#14b8a6;">Ukloole Team</span></h1>
<p style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.75);max-width:600px;margin:0 auto;line-height:1.7;">Help us build the future of customer service in Africa and beyond.</p>
</div></section>
<section class="py-5" style="background:#f8fafc"><div class="container">
<?php if(empty($jobs)): ?>
<div class="text-center py-5"><i class="bi bi-briefcase" style="font-size:4rem;color:#cbd5e1"></i><h3 class="mt-3" style="color:#64748b">No open positions right now</h3><p style="color:#94a3b8">Check back soon or send your CV to <a href="mailto:info@ukloole.com">info@ukloole.com</a></p></div>
<?php else: ?>
<div class="row g-4">
<?php $typeLabel=['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','remote'=>'Remote']; ?>
<?php foreach($jobs as $j): ?>
<div class="col-lg-6">
<div class="service-card h-100">
<div class="d-flex justify-content-between align-items-start mb-3">
<div>
<span class="badge" style="background:rgba(99,102,241,.1);color:#6366f1;border-radius:50px;padding:5px 14px;font-size:.8rem"><?= $typeLabel[$j['type']] ?? $j['type'] ?></span>
<?php if($j['department']): ?><span class="badge ms-2" style="background:#f1f5f9;color:#64748b;border-radius:50px;padding:5px 14px;font-size:.8rem"><?= h($j['department']) ?></span><?php endif; ?>
</div>
<span style="color:#94a3b8;font-size:.85rem"><i class="bi bi-geo-alt me-1"></i><?= h($j['location']??'Remote') ?></span>
</div>
<h4 style="font-family:Poppins,sans-serif;font-weight:700;color:#0f172a"><?= h($j['title']) ?></h4>
<?php if($j['description']): ?><p style="color:#64748b;font-size:.95rem"><?= h(substr(trim(html_entity_decode(strip_tags($j['description']), ENT_QUOTES, 'UTF-8')),0,200)) ?>...</p><?php endif; ?>
<?php if($j['salary_range']): ?><p style="color:#6366f1;font-weight:600"><i class="bi bi-cash me-1"></i><?= h($j['salary_range']) ?></p><?php endif; ?>
<a href="/job-details.php?id=<?= $j['id'] ?>" class="btn btn-primary-custom">View & Apply <i class="bi bi-arrow-right ms-2"></i></a>
</div></div>
<?php endforeach; ?>
</div><?php endif; ?>
</div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>