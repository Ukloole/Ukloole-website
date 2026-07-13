<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$id = (int)($_GET['id'] ?? 0);
if(!$id) { header('Location: /careers.php'); exit; }
$stmt = db()->prepare("SELECT * FROM jobs WHERE id=? AND is_active=1");
$stmt->execute([$id]);
$job = $stmt->fetch();
if(!$job) { header('Location: /careers.php'); exit; }
$pageTitle = $job['title'];

$appMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $letter  = trim($_POST['cover_letter'] ?? '');
    $cv_link = trim($_POST['cv_link'] ?? '');
    $cv_file = null;

    // Handle CV file upload
    if(!empty($_FILES['cv_file']['tmp_name']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK){
        $allowed_ext = ['pdf','doc','docx'];
        $ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed_ext) && $_FILES['cv_file']['size'] <= 5 * 1024 * 1024){
            $filename  = 'cv_' . time() . '_' . substr(md5(rand()), 0, 8) . '.' . $ext;
            $uploadDir = __DIR__ . '/uploads/cvs/';
            if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['cv_file']['tmp_name'], $uploadDir . $filename);
            $cv_file = '/uploads/cvs/' . $filename;
        }
    }

    if ($name && $email) {
        $stmt2 = db()->prepare("INSERT INTO applications (job_id,name,email,phone,cover_letter,cv_file,cv_link) VALUES (?,?,?,?,?,?,?)");
        $stmt2->execute([$id,$name,$email,$phone,$letter,$cv_file,$cv_link]);
        $appMsg = 'success';

        $vars = [
            'name'         => h($name),
            'job_title'    => h($job['title']),
            'email'        => h($email),
            'phone'        => h($phone ?: 'â€”'),
            'cover_letter' => nl2br(h($letter ?: 'â€”')),
            'details'      => ($cv_link ? '<p><strong>CV link:</strong> ' . h($cv_link) . '</p>' : '')
                             . ($cv_file ? '<p><strong>CV file:</strong> ' . SITE_URL . h($cv_file) . '</p>' : ''),
        ];

        // Confirmation email to the applicant
        sendTemplatedMail($email, $name, 'application_received', $vars,
            'We received your application - ' . $job['title'],
            '<p>Hi ' . h($name) . ',</p>
            <p>Thanks for applying for the <strong>' . h($job['title']) . '</strong> role at Ukloole.
            We have received your application and our team will review it shortly.
            We will be in touch if your profile is a match.</p>
            <p>Best,<br>The Ukloole Team</p>'
        );

        // Notification email to the admin
        sendTemplatedMail(ADMIN_NOTIFY_EMAIL, 'Ukloole Admin', 'admin_notification', $vars,
            'New application: ' . $job['title'],
            '<p>A new application was submitted for <strong>' . h($job['title']) . '</strong>.</p>
            <p><strong>Name:</strong> ' . h($name) . '<br>
            <strong>Email:</strong> ' . h($email) . '<br>
            <strong>Phone:</strong> ' . h($phone) . '</p>
            <p><strong>Cover letter:</strong><br>' . nl2br(h($letter)) . '</p>'
            . ($cv_link ? '<p><strong>CV link:</strong> ' . h($cv_link) . '</p>' : '')
            . ($cv_file ? '<p><strong>CV file:</strong> ' . SITE_URL . h($cv_file) . '</p>' : '')
        );
    } else { $appMsg = 'error'; }
}
require_once __DIR__ . '/includes/header.php';
$typeLabel=['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','remote'=>'Remote'];
?>
<section class="hero-section" style="min-height:35vh;background-image:url('/assets/images/pixabay.jpg');background-size:cover;background-position:center;background-repeat:no-repeat;"><div class="hero-overlay"></div>
<div class="container d-flex align-items-center" style="min-height:35vh">
<div class="hero-content w-100" style="padding-top:100px;padding-bottom:3rem;">
<a href="/careers.php" style="color:rgba(255,255,255,.7);text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px"><i class="bi bi-arrow-left"></i>Back to Careers</a>
<span class="badge-premium me-2"><?= $typeLabel[$job['type']]??$job['type'] ?></span>
<h1 class="hero-title mt-2"><?= h($job['title']) ?></h1>
<p style="color:rgba(255,255,255,.8)"><i class="bi bi-geo-alt me-1"></i><?= h($job['location']??'Remote') ?><?php if($job['department']): ?> ¡¤ <?= h($job['department']) ?><?php endif; ?></p>
</div></div></section>
<section class="py-5"><div class="container"><div class="row g-5">
<div class="col-lg-7">
<div class="service-card">
<?php if($job['description']): ?><h4 class="fw-bold mb-3">About This Role</h4><div style="color:#334155;line-height:1.8"><?= nl2br(safeHtml($job['description'])) ?></div><?php endif; ?>
<?php if($job['requirements']): ?><h4 class="fw-bold mb-3 mt-4">Requirements</h4><div style="color:#334155;line-height:1.8"><?= nl2br(safeHtml($job['requirements'])) ?></div><?php endif; ?>
<?php if($job['salary_range']): ?><p class="mt-4" style="color:#6366f1;font-weight:600;font-size:1.1rem"><i class="bi bi-cash me-2"></i>Salary: <?= h($job['salary_range']) ?></p><?php endif; ?>
</div>
</div>
<div class="col-lg-5">
<div class="service-card">
<h4 class="fw-bold mb-4"><i class="bi bi-send me-2" style="color:#6366f1"></i>Apply for This Role</h4>
<?php if($appMsg==='success'): ?><div class="alert alert-success">Application submitted! We'll be in touch soon.</div>
<?php elseif($appMsg==='error'): ?><div class="alert alert-danger">Please fill in name and email.</div>
<?php else: ?>
<form method="post" enctype="multipart/form-data">
<div class="mb-3"><label class="form-label fw-600">Full Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-600">Email *</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-600">Phone</label><input type="tel" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label fw-600">Cover Letter</label><textarea name="cover_letter" class="form-control" rows="5" placeholder="Tell us why you're a great fit..."></textarea></div>
<div class="mb-3">
  <label class="form-label fw-600">Upload CV / Resume</label>
  <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx">
  <small class="text-muted">PDF, DOC or DOCX ¡ª max 5MB</small>
</div>
<div class="mb-3" style="text-align:center;color:#94a3b8;font-size:.85rem;">¡ª or paste a link instead ¡ª</div>
<div class="mb-3">
  <label class="form-label fw-600">CV / Portfolio Link</label>
  <input type="url" name="cv_link" class="form-control" placeholder="https://drive.google.com/... or LinkedIn, etc">
</div>
<button type="submit" class="btn btn-primary-custom w-100">Submit Application <i class="bi bi-arrow-right ms-2"></i></button>
</form>
<?php endif; ?>
</div>
</div>
</div></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>