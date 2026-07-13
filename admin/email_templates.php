<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('email_templates');
$db = db();

// Safe migration — create table + seed defaults if missing (so this page
// never breaks even if the SQL migration hasn't been run yet).
$db->exec("CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_key` varchar(50) NOT NULL,
  `label` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_key` (`template_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$defaults = [
    'application_received' => ['Application Received (to applicant)', 'We received your application - {{job_title}}',
        "<p>Hi {{name}},</p>\n<p>Thanks for applying for the <strong>{{job_title}}</strong> role at Ukloole. We have received your application and our team will review it shortly. We will be in touch if your profile is a match.</p>\n<p>Best,<br>The Ukloole Team</p>"],
    'admin_notification' => ['New Application Alert (to admin)', 'New application: {{job_title}}',
        "<p>A new application was submitted for <strong>{{job_title}}</strong>.</p>\n<p><strong>Name:</strong> {{name}}<br>\n<strong>Email:</strong> {{email}}<br>\n<strong>Phone:</strong> {{phone}}</p>\n<p><strong>Cover letter:</strong><br>{{cover_letter}}</p>\n{{details}}"],
    'shortlisted' => ['Shortlisted', "You've been shortlisted - {{job_title}}",
        "<p>Hi {{name}},</p>\n<p>Good news! You have been <strong>shortlisted</strong> for the <strong>{{job_title}}</strong> role at Ukloole.</p>\n<p>As a next step, please go through our training videos here: <a href=\"{{training_link}}\">{{training_link}}</a></p>\n<p>Our team will follow up with further instructions soon.</p>\n<p>Best,<br>The Ukloole Team</p>"],
    'interview' => ['Interview Invitation', 'Interview invitation - {{job_title}}',
        "<p>Hi {{name}},</p>\n<p>Great news! We would like to invite you for an <strong>interview</strong> for the <strong>{{job_title}}</strong> role at Ukloole.</p>\n<p>Our team will reach out shortly with the date, time, and format (video call or phone) for your interview. If you have any scheduling constraints, feel free to reply to this email and let us know.</p>\n<p>Best,<br>The Ukloole Team</p>"],
    'onboarding' => ['Onboarding / Talent Pool', 'Welcome to Ukloole - {{job_title}}',
        "<p>Hi {{name}},</p>\n<p>Congratulations! You have successfully passed our process for the <strong>{{job_title}}</strong> role, and you are now part of the Ukloole talent pool.</p>\n<p>Next, please complete onboarding using the link below: <a href=\"{{onboarding_link}}\">{{onboarding_link}}</a></p>\n<p>Our team will guide you through the remaining steps to get you started.</p>\n<p>Welcome aboard!<br>The Ukloole Team</p>"],
    'rejected' => ['Rejection', 'Update on your application - {{job_title}}',
        "<p>Hi {{name}},</p>\n<p>Thank you for applying for the <strong>{{job_title}}</strong> role at Ukloole, and for the time you invested in your application.</p>\n<p>After careful review, we have decided not to move forward with your application at this time. This is not a reflection of your abilities, and we encourage you to apply for future roles that match your experience.</p>\n<p>We wish you the best in your search.</p>\n<p>Best,<br>The Ukloole Team</p>"],
];
$ins = $db->prepare("INSERT IGNORE INTO email_templates (template_key,label,subject,body) VALUES (?,?,?,?)");
foreach ($defaults as $key => [$label, $subject, $body]) {
    $ins->execute([$key, $label, $subject, $body]);
}

// Which placeholders are relevant to each template — shown as hints in the UI
$placeholders = [
    'application_received' => ['name', 'job_title'],
    'admin_notification'   => ['name', 'job_title', 'email', 'phone', 'cover_letter', 'details'],
    'shortlisted'           => ['name', 'job_title', 'training_link'],
    'interview'             => ['name', 'job_title'],
    'onboarding'            => ['name', 'job_title', 'onboarding_link'],
    'rejected'              => ['name', 'job_title'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['template_key'])) {
    $key     = trim($_POST['template_key']);
    $subject = trim($_POST['subject'] ?? '');
    $body    = $_POST['body'] ?? '';
    if ($key && $subject && $body) {
        $db->prepare("UPDATE email_templates SET subject=?, body=? WHERE template_key=?")
           ->execute([$subject, $body, $key]);
    }
    redirect('/admin/email_templates.php?saved=' . urlencode($key));
}

$rows = $db->query("SELECT * FROM email_templates ORDER BY FIELD(template_key,'application_received','admin_notification','shortlisted','interview','onboarding','rejected')")->fetchAll();
adminHeader('Email Templates', 'email_templates');
?>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success mb-4"><i class="bi bi-check-circle me-1"></i> "<?= h($defaults[$_GET['saved']][0] ?? $_GET['saved']) ?>" template saved.</div>
<?php endif; ?>

<p class="text-muted mb-4">These are the automatic emails sent to applicants (and to you) as you move them through the pipeline. Edit the subject and message below — use the placeholder tags shown under each one and they'll be swapped for the real values when the email goes out.</p>

<div class="accordion" id="templatesAccordion">
<?php foreach ($rows as $i => $r):
    $key  = $r['template_key'];
    $tags = $placeholders[$key] ?? [];
?>
  <div class="admin-table mb-3">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#tpl-<?= h($key) ?>">
      <div>
        <h6 class="fw-bold mb-0"><?= h($r['label']) ?></h6>
        <small class="text-muted">Key: <?= h($key) ?></small>
      </div>
      <i class="bi bi-chevron-down"></i>
    </div>
    <div id="tpl-<?= h($key) ?>" class="collapse<?= $i === 0 ? ' show' : '' ?>">
      <form method="post" class="p-3">
        <input type="hidden" name="template_key" value="<?= h($key) ?>">

        <div class="mb-2">
          <label class="form-label fw-semibold">Subject</label>
          <input type="text" name="subject" class="form-control" value="<?= h($r['subject']) ?>" required>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Message (HTML allowed)</label>
          <textarea name="body" class="form-control" rows="8" required><?= h($r['body']) ?></textarea>
        </div>

        <?php if ($tags): ?>
        <div class="mb-3">
          <small class="text-muted">Available placeholders: </small>
          <?php foreach ($tags as $t): ?>
            <code class="me-1" style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:.8rem;">{{<?= h($t) ?>}}</code>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save me-1"></i>Save Template</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="stat-card mt-2">
  <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>About the links</h6>
  <p class="text-muted mb-0" style="font-size:.9rem;">
    The <code>{{training_link}}</code> and <code>{{onboarding_link}}</code> placeholders pull from
    <code>TRAINING_VIDEOS_URL</code> and <code>ONBOARDING_URL</code>, which are set in <code>includes/config.php</code>.
    Update those two lines whenever the actual links change, and every email using them will update automatically.
  </p>
</div>

<?php adminFooter(); ?>
