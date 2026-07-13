<?php
// ============================================================
//  UKLOOLE — Configuration
//  Edit DB_* values to match your cPanel MySQL credentials
// ============================================================
define('DB_HOST',    'localhost');
define('DB_NAME',    'ukloolec_mainpg_ukloole');     // e.g. cpuser_ukloole
define('DB_USER',    'ukloolec_mainpg_user');     // e.g. cpuser_admin
define('DB_PASS',    'Admin4ukloole');
define('DB_CHARSET', 'utf8mb4');
define('ADMIN_SALT', 'ukloole_salt_2025');
define('SITE_URL',   'https://ukloole.com');  // no trailing slash
define('SITE_NAME',  'Ukloole');

// ── Email / SMTP (edit these to match your mailbox) ────────
define('SMTP_HOST',      'mail.ukloole.com');       // e.g. mail.ukloole.com or smtp.gmail.com
define('SMTP_PORT',      465);                      // 465 for SSL, 587 for TLS
define('SMTP_SECURE',    'ssl');                     // 'ssl' or 'tls'
define('SMTP_USERNAME',  'careers@ukloole.com');    // mailbox login
define('SMTP_PASSWORD',  'Admin4ukloole');               // mailbox password
define('SMTP_FROM_EMAIL','careers@ukloole.com');
define('SMTP_FROM_NAME', 'Ukloole');
define('ADMIN_NOTIFY_EMAIL', 'careers@ukloole.com');    // where admin notifications go
define('TRAINING_VIDEOS_URL', 'https://ukloole.com/training');   // link sent to shortlisted applicants
define('ONBOARDING_URL', 'https://ukloole.com/onboarding');      // link sent to onboarding-stage applicants

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

/**
 * Fetch an editable email template from the database.
 * Returns ['subject'=>..., 'body'=>...] or null if not found.
 */
function getEmailTemplate(string $key): ?array {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = db()->prepare("SELECT subject, body FROM email_templates WHERE template_key=?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $cache[$key] = ($row ?: null);
    } catch (\Throwable $e) {
        return $cache[$key] = null;
    }
}

/** Replace {{placeholder}} tokens in a template string with real values. */
function renderTemplate(string $text, array $vars): string {
    foreach ($vars as $k => $v) {
        $text = str_replace('{{' . $k . '}}', $v, $text);
    }
    return $text;
}

/** Fetch + render a stored email template in one call, with a hardcoded fallback if the row is missing. */
function sendTemplatedMail(string $toEmail, string $toName, string $templateKey, array $vars, string $fallbackSubject, string $fallbackBody): bool {
    $tpl = getEmailTemplate($templateKey);
    $subject = renderTemplate($tpl['subject'] ?? $fallbackSubject, $vars);
    $body    = renderTemplate($tpl['body']    ?? $fallbackBody,    $vars);
    return sendMail($toEmail, $toName, $subject, $body);
}
function sendMail(string $toEmail, string $toName, string $subject, string $bodyHtml): bool {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('sendMail failed: ' . $e->getMessage());
        return false;
    }
}

// ── DB connection (singleton) ──────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Helpers ────────────────────────────────────────────────
function hashPw(string $pw): string { return hash('sha256', $pw . ADMIN_SALT); }
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

/**
 * Sanitize rich-text HTML for safe display (allows basic formatting only,
 * strips all attributes so no scripts/event handlers can sneak through).
 */
function safeHtml(string $html): string {
    $allowed = '<b><strong><i><em><u><ul><ol><li><br><p><div><span>';
    $html = strip_tags($html, $allowed);
    $html = preg_replace('/<(\/?)(\w+)[^>]*>/', '<$1$2>', $html);
    return $html;
}
function redirect(string $url): void { header("Location: $url"); exit; }

function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_id'])) redirect('/admin/login.php');
}

/** List of admin nav pages that can be individually granted to non-admin (staff) users. */
const GRANTABLE_PAGES = ['quotes','tickets','subscribers','testimonials','blog','tools','jobs','applications','email_templates'];

/** Does the currently logged-in user have access to this admin page? */
function userCan(string $page): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (($_SESSION['admin_role'] ?? 'staff') === 'admin') return true; // admins see everything
    if ($page === 'dashboard') return true; // dashboard is always visible once logged in
    if ($page === 'users') return false;    // user management is admin-only, never grantable
    $allowed = array_filter(array_map('trim', explode(',', $_SESSION['admin_permissions'] ?? '')));
    return in_array($page, $allowed, true);
}

/** Call after requireAdmin() on any page that isn't open to all logged-in users. */
function requirePagePermission(string $page): void {
    requireAdmin();
    if (!userCan($page)) redirect('/admin/dashboard.php');
}

function flash(string $key, string $msg = ''): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if ($msg) { $_SESSION["flash_$key"] = $msg; return ''; }
    $m = $_SESSION["flash_$key"] ?? '';
    unset($_SESSION["flash_$key"]);
    return $m;
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)   return "$diff seconds ago";
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hrs ago';
    return date('M j, Y', strtotime($datetime));
}
