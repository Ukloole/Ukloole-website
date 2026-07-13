<?php
define('UKLOOLE', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin-layout.php';
requireAdmin();
requirePagePermission('applications');
$db = db();

$emailSentLabel = null;
$emailSentTo    = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!empty($_POST['delete_id'])) $db->prepare("DELETE FROM applications WHERE id=?")->execute([(int)$_POST['delete_id']]);
    elseif(!empty($_POST['status_id'])){
        $newStatus = trim($_POST['new_status']);
        $appId     = (int)$_POST['status_id'];

        $row = $db->prepare("SELECT a.*, j.title AS job_title FROM applications a LEFT JOIN jobs j ON a.job_id=j.id WHERE a.id=?");
        $row->execute([$appId]);
        $app = $row->fetch();

        $db->prepare("UPDATE applications SET status=? WHERE id=?")->execute([$newStatus,$appId]);

        if($app && $newStatus !== $app['status']){
            $jobTitle = $app['job_title'] ?? 'the role you applied for';
            $vars = [
                'name'             => h($app['name']),
                'job_title'        => h($jobTitle),
                'training_link'    => TRAINING_VIDEOS_URL,
                'onboarding_link'  => ONBOARDING_URL,
            ];

            $sent = false;

            if($newStatus === 'shortlisted'){
                $sent = sendTemplatedMail($app['email'], $app['name'], 'shortlisted', $vars,
                    "You've been shortlisted - $jobTitle",
                    '<p>Hi ' . h($app['name']) . ',</p>
                    <p>Good news! You have been <strong>shortlisted</strong> for the <strong>' . h($jobTitle) . '</strong> role at Ukloole.</p>
                    <p>As a next step, please go through our training videos here: <a href="' . TRAINING_VIDEOS_URL . '">' . TRAINING_VIDEOS_URL . '</a></p>
                    <p>Our team will follow up with further instructions soon.</p>
                    <p>Best,<br>The Ukloole Team</p>'
                );
            } elseif($newStatus === 'interview'){
                $sent = sendTemplatedMail($app['email'], $app['name'], 'interview', $vars,
                    "Interview invitation - $jobTitle",
                    '<p>Hi ' . h($app['name']) . ',</p>
                    <p>Great news! We would like to invite you for an <strong>interview</strong> for the <strong>' . h($jobTitle) . '</strong> role at Ukloole.</p>
                    <p>Our team will reach out shortly with the date, time, and format (video call or phone) for your interview. If you have any scheduling constraints, feel free to reply to this email and let us know.</p>
                    <p>Best,<br>The Ukloole Team</p>'
                );
            } elseif($newStatus === 'onboarding'){
                $sent = sendTemplatedMail($app['email'], $app['name'], 'onboarding', $vars,
                    "Welcome to Ukloole - $jobTitle",
                    '<p>Hi ' . h($app['name']) . ',</p>
                    <p>Congratulations! You have successfully passed our process for the <strong>' . h($jobTitle) . '</strong> role, and you are now part of the Ukloole talent pool.</p>
                    <p>Next, please complete onboarding using the link below: <a href="' . ONBOARDING_URL . '">' . ONBOARDING_URL . '</a></p>
                    <p>Our team will guide you through the remaining steps to get you started.</p>
                    <p>Welcome aboard!<br>The Ukloole Team</p>'
                );
            } elseif($newStatus === 'rejected'){
                $sent = sendTemplatedMail($app['email'], $app['name'], 'rejected', $vars,
                    "Update on your application - $jobTitle",
                    '<p>Hi ' . h($app['name']) . ',</p>
                    <p>Thank you for applying for the <strong>' . h($jobTitle) . '</strong> role at Ukloole, and for the time you invested in your application.</p>
                    <p>After careful review, we have decided not to move forward with your application at this time. This is not a reflection of your abilities, and we encourage you to apply for future roles that match your experience.</p>
                    <p>We wish you the best in your search.</p>
                    <p>Best,<br>The Ukloole Team</p>'
                );
            }
            // 'reviewing' and 'new' -> no email sent

            if($sent){
                $db->prepare("INSERT INTO application_emails (application_id, template_key) VALUES (?,?)")->execute([$appId, $newStatus]);
                $emailSentLabel = ucfirst($newStatus);
                $emailSentTo    = $app['name'];
            }
        }

        $redirectUrl = '/admin/applications.php';
        if ($emailSentLabel) $redirectUrl .= '?email_sent=' . urlencode($emailSentLabel) . '&applicant=' . urlencode($emailSentTo);
        redirect($redirectUrl);
    }
    redirect('/admin/applications.php');
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $db->prepare("SELECT a.*,j.title AS job_title FROM applications a LEFT JOIN jobs j ON a.job_id=j.id WHERE a.email LIKE ? OR a.name LIKE ? ORDER BY a.created_at DESC");
    $stmt->execute(['%'.$q.'%', '%'.$q.'%']);
    $apps = $stmt->fetchAll();
} else {
    $apps = $db->query("SELECT a.*,j.title AS job_title FROM applications a LEFT JOIN jobs j ON a.job_id=j.id ORDER BY a.created_at DESC")->fetchAll();
}

// Build a per-applicant email history so HR can see what's already been sent
$db->exec("CREATE TABLE IF NOT EXISTS `application_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `template_key` varchar(50) NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$emailLog = [];
$logRows = $db->query("SELECT application_id, template_key, MAX(sent_at) AS last_sent FROM application_emails GROUP BY application_id, template_key")->fetchAll();
foreach ($logRows as $lr) { $emailLog[$lr['application_id']][] = $lr; }

adminHeader('Applications','applications');
?>
<?php if(!empty($_GET['email_sent'])): ?>
<script>alert(<?= json_encode($_GET['email_sent'] . ' email sent to ' . ($_GET['applicant'] ?? 'the applicant') . '.') ?>);</script>
<?php endif; ?>
<script>
const EMAIL_LABELS = {
  shortlisted: 'Shortlisted (training video link)',
  interview:   'Interview invitation',
  onboarding:  'Onboarding / welcome to talent pool',
  rejected:    'Rejection'
};
function confirmStatusChange(sel){
  const newStatus = sel.value;
  const original  = sel.getAttribute('data-original');
  const name      = sel.getAttribute('data-name');
  if (EMAIL_LABELS[newStatus]) {
    const ok = confirm('Send the "' + EMAIL_LABELS[newStatus] + '" email to ' + name + '?');
    if (!ok) { sel.value = original; return; }
  }
  sel.form.submit();
}
</script>
<div class="admin-table">
<div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
<h6 class="fw-bold mb-0">Job Applications (<?= count($apps) ?>)</h6>
<form method="get" class="d-flex gap-2">
<input type="text" name="q" class="form-control form-control-sm" style="width:240px" placeholder="Search by email or name" value="<?= h($q) ?>">
<button type="submit" class="btn btn-sm btn-primary-custom"><i class="bi bi-search"></i></button>
<?php if($q !== ''): ?><a href="/admin/applications.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
</form>
</div>
<table class="table table-hover mb-0">
<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Applied For</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php if(empty($apps)): ?><tr><td colspan="7" class="text-center py-4 text-muted"><?= $q !== '' ? 'No applications match "' . h($q) . '"' : 'No applications yet' ?></td></tr>
<?php else: foreach($apps as $a): ?>
<tr>
<td><?= $a['id'] ?></td>
<td><strong><?= h($a['name']) ?></strong><?php if($a['phone']): ?><br><small class="text-muted"><?= h($a['phone']) ?></small><?php endif; ?></td>
<td><a href="mailto:<?= h($a['email']) ?>"><?= h($a['email']) ?></a></td>
<td><?= h($a['job_title']??'Unknown Position') ?></td>
<td>
<form method="post" class="d-inline">
<input type="hidden" name="status_id" value="<?= $a['id'] ?>">
<select name="new_status" data-original="<?= h($a['status']) ?>" data-name="<?= h($a['name']) ?>" onchange="confirmStatusChange(this)" class="form-select form-select-sm" style="width:auto;font-size:.8rem">
<?php foreach(['new','reviewing','shortlisted','interview','onboarding','rejected'] as $s): ?><option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
</select>
</form>
<?php if (!empty($emailLog[$a['id']])): ?>
<div class="mt-1">
<?php foreach ($emailLog[$a['id']] as $log): ?>
<span class="badge bg-light text-dark border me-1 mb-1" style="font-size:.68rem;font-weight:500" title="Sent <?= date('M j, Y g:ia', strtotime($log['last_sent'])) ?>">
<i class="bi bi-envelope-check-fill text-success"></i> <?= h(ucfirst($log['template_key'])) ?>
</span>
<?php endforeach; ?>
</div>
<?php endif; ?>
</td>
<td><?= date('M j, Y',strtotime($a['created_at'])) ?></td>
<td>
<?php if($a['cover_letter']): ?><button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#modal<?= $a['id'] ?>" title="View Cover Letter"><i class="bi bi-file-text"></i></button>
<div class="modal fade" id="modal<?= $a['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-4"><h5>Cover Letter — <?= h($a['name']) ?></h5><p style="white-space:pre-wrap"><?= h($a['cover_letter']) ?></p><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div><?php endif; ?>
<?php if(!empty($a['cv_file'])): ?><a href="<?= h($a['cv_file']) ?>" target="_blank" class="btn btn-sm btn-outline-success me-1" title="Download CV"><i class="bi bi-download"></i> CV</a><?php endif; ?>
<?php if(!empty($a['cv_link'])): ?><a href="<?= h($a['cv_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1" title="View CV Link"><i class="bi bi-link-45deg"></i> CV Link</a><?php endif; ?>
<form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
<input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
</form>
</td>
</tr>
<?php endforeach; endif; ?>
</tbody></table></div>
<?php adminFooter(); ?>
