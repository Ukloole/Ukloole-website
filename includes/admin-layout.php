<?php
if(!defined('UKLOOLE')) die();

function adminHeader(string $title, string $active = ''): void {
    if(session_status()===PHP_SESSION_NONE) session_start();
    $nav = [
        'dashboard'    => ['bi-speedometer2','Dashboard','/admin/dashboard.php'],
        'quotes'       => ['bi-file-text','Quotes','/admin/quotes.php'],
        'tickets'      => ['bi-ticket-perforated','Tickets','/admin/tickets.php'],
        'subscribers'  => ['bi-envelope','Subscribers','/admin/subscribers.php'],
        'testimonials' => ['bi-chat-quote','Testimonials','/admin/testimonials.php'],
        'blog'         => ['bi-journal-richtext','Blog Posts','/admin/blog.php'],
        'tools'        => ['bi-tools','Tools','/admin/tools.php'],
        'jobs'         => ['bi-briefcase','Jobs','/admin/jobs.php'],
        'applications' => ['bi-people','Applications','/admin/applications.php'],
        'email_templates' => ['bi-envelope-paper','Email Templates','/admin/email_templates.php'],
        'users'        => ['bi-person-gear','Users','/admin/users.php'],
    ];
    if (($_SESSION['admin_role'] ?? 'staff') !== 'admin') {
        $nav = array_filter($nav, fn($key) => userCan($key), ARRAY_FILTER_USE_KEY);
    }
    echo '<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>' . htmlspecialchars($title) . ' — Ukloole Admin</title>
    <link rel="icon" href="/assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    </head><body>
    <div class="admin-wrapper">
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="brand">
        <img src="/assets/images/logo.png" alt="Ukloole">
        <span>Ukloole</span>
      </div>
      <nav class="admin-nav">
        <div class="section-label">Main</div>';
    foreach ($nav as $key => [$icon, $label, $href]) {
        $cls = $key === $active ? ' active' : '';
        echo "<a href=\"$href\" class=\"$cls\"><i class=\"bi $icon\"></i>$label</a>";
    }
    echo '<div class="section-label mt-3">Account</div>
        <a href="/" target="_blank"><i class="bi bi-globe"></i>View Site</a>
        <a href="/admin/logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a>
      </nav>
    </aside>
    <div class="admin-content">
      <div class="admin-topbar">
        <h1>' . htmlspecialchars($title) . '</h1>
        <div style="color:#64748b;font-size:.9rem">';
    if(session_status()===PHP_SESSION_NONE) session_start();
    echo 'Logged in as <strong>' . htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') . '</strong>';
    echo '</div></div><div class="admin-main">';
}

function adminFooter(): void {
    echo '</div></div></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body></html>';
}
