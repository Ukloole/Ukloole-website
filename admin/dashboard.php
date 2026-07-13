<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
$db = db();
$stats = [
    'quotes'      => (int)$db->query("SELECT COUNT(*) FROM quotes")->fetchColumn(),
    'subscribers' => (int)$db->query("SELECT COUNT(*) FROM subscribers WHERE status='active'")->fetchColumn(),
    'tickets'     => (int)$db->query("SELECT COUNT(*) FROM tickets WHERE status='open'")->fetchColumn(),
    'posts'       => (int)$db->query("SELECT COUNT(*) FROM blog_posts WHERE published=1")->fetchColumn(),
    'jobs'        => (int)$db->query("SELECT COUNT(*) FROM jobs WHERE is_active=1")->fetchColumn(),
    'applications'=> (int)$db->query("SELECT COUNT(*) FROM applications WHERE status='new'")->fetchColumn(),
];
$recentQuotes  = $db->query("SELECT * FROM quotes ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentTickets = $db->query("SELECT * FROM tickets ORDER BY created_at DESC LIMIT 5")->fetchAll();
adminHeader('Dashboard', 'dashboard');
?>
<div class="row g-4 mb-4">
<?php $statCards=[
    ['bi-file-text','Quotes Received',$stats['quotes'],'#6366f1'],
    ['bi-envelope','Active Subscribers',$stats['subscribers'],'#10b981'],
    ['bi-ticket-perforated','Open Tickets',$stats['tickets'],'#f59e0b'],
    ['bi-journal-richtext','Published Posts',$stats['posts'],'#8b5cf6'],
    ['bi-briefcase','Active Jobs',$stats['jobs'],'#06b6d4'],
    ['bi-people','New Applications',$stats['applications'],'#ec4899'],
]; ?>
<?php foreach($statCards as [$icon,$label,$val,$color]): ?>
<div class="col-md-4 col-lg-2">
<div class="stat-card d-flex align-items-center gap-3">
    <div class="stat-icon" style="color:<?= $color ?>"><?= "<i class='bi $icon'></i>" ?></div>
    <div><div class="stat-num"><?= $val ?></div><div class="stat-label"><?= $label ?></div></div>
</div></div>
<?php endforeach; ?>
</div>

<div class="row g-4">
<div class="col-lg-6">
<div class="admin-table">
<div class="d-flex align-items-center justify-content-between p-3 border-bottom">
<h6 class="fw-bold mb-0">Recent Quotes</h6>
<a href="/admin/quotes.php" class="btn btn-sm btn-outline-primary">View All</a>
</div>
<table class="table table-hover mb-0">
<thead><tr><th>Name</th><th>Email</th><th>Date</th><th>Status</th></tr></thead>
<tbody>
<?php if(empty($recentQuotes)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No quotes yet</td></tr>
<?php else: foreach($recentQuotes as $q): ?>
<tr><td><?= h($q['name']) ?></td><td><?= h($q['email']) ?></td>
<td><?= date('M j',strtotime($q['created_at'])) ?></td>
<td><span class="badge-status badge-<?= $q['status'] ?>"><?= ucfirst($q['status']) ?></span></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>

<div class="col-lg-6">
<div class="admin-table">
<div class="d-flex align-items-center justify-content-between p-3 border-bottom">
<h6 class="fw-bold mb-0">Recent Tickets</h6>
<a href="/admin/tickets.php" class="btn btn-sm btn-outline-primary">View All</a>
</div>
<table class="table table-hover mb-0">
<thead><tr><th>Subject</th><th>Email</th><th>Date</th><th>Status</th></tr></thead>
<tbody>
<?php if(empty($recentTickets)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No tickets yet</td></tr>
<?php else: foreach($recentTickets as $t): ?>
<tr><td><?= h(substr($t['subject'],0,30)) ?>...</td><td><?= h($t['email']) ?></td>
<td><?= date('M j',strtotime($t['created_at'])) ?></td>
<td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td></tr>
<?php endforeach; endif; ?>
</tbody></table></div></div>
</div>
<?php adminFooter(); ?>
