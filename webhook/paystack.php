<?php
// ============================================================
// UKLOOLE — UNIFIED PAYSTACK WEBHOOK
// Location: public_html/webhook/paystack.php
//
// Set this ONE URL in your Paystack dashboard:
//   https://ukloole.com/webhook/paystack.php
//
// Handles payments for ALL Ukloole products:
//
//   CV Builder (DB: ukloolec_cvbuilder)
//     - purchases     (one-time CV download)   ref prefix: cv_
//     - subscriptions (monthly/yearly access)  ref prefix: sub_
//
//   Learning Hub (DB: ukloolec_learninghub)
//     - orders        (materials / cart)        ref prefix: lh_mat_
//     - community     (premium membership)      ref prefix: lh_com_
//
// Routing precedence:
//   1. metadata.app + metadata.purchase_type  (set in JS — most reliable)
//   2. reference prefix
//   3. database lookup fallback (for legacy references)
//
// To add a new product in future: add a new ref prefix and
// a new fulfilXxx() function at the bottom of this file.
// ============================================================

// ============================================================
// CONFIG — CV Builder database
// ============================================================
define('CV_DB_HOST',    'localhost');
define('CV_DB_NAME',    'ukloolec_cvbuilder');
define('CV_DB_USER',    'ukloolec_cvuser');
define('CV_DB_PASS',    'Admin4ukloole');       // ← your CV Builder DB password
define('CV_DB_CHARSET', 'utf8mb4');

// Paystack secret key — loaded from the same protected file used by cv-builder-app
// so you only ever have to update it in one place.
$_paystackKeysFile = '/home/ukloolec/paystack_keys.php';
if (file_exists($_paystackKeysFile)) {
    require_once $_paystackKeysFile;
}
// Fallback: define it directly here if the file above doesn't exist
if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', 'sk_live_REPLACE_IF_FILE_MISSING');
}

// ============================================================
// CONFIG — Learning Hub database
// ============================================================
define('LH_DB_HOST', 'localhost');
define('LH_DB_NAME', 'ukloolec_learninghub');
define('LH_DB_USER', 'ukloolec_lbuser');
define('LH_DB_PASS', 'Admin4ukloole');         // ← your Learning Hub DB password
define('LH_SITE_URL', 'https://learn.ukloole.com');

// ============================================================
// 1. VERIFY PAYSTACK SIGNATURE
// ============================================================
$input     = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$signature || $signature !== hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY)) {
    http_response_code(401);
    exit('Invalid signature');
}

$event = json_decode($input, true);
if (!$event) { http_response_code(400); exit('Invalid payload'); }

if (($event['event'] ?? '') !== 'charge.success') {
    http_response_code(200);
    exit('OK (ignored event)');
}

$data       = $event['data'] ?? [];
$reference  = $data['reference'] ?? '';
$metaApp    = strtolower(trim($data['metadata']['app']           ?? ''));
$metaType   = strtolower(trim($data['metadata']['purchase_type'] ?? ''));
$customerEm = $data['customer']['email'] ?? '';

// Read duration_days from metadata (used for community tier access length)
$metaDays          = isset($data['metadata']['duration_days']) ? (int)$data['metadata']['duration_days'] : 0;
$LH_COMMUNITY_DAYS = $metaDays > 0 ? $metaDays : 90; // default 90 days if not passed

if (!$reference) { http_response_code(400); exit('No reference'); }

// ============================================================
// 2. DETERMINE TARGET
// ============================================================
$target = '';

// (a) Metadata routing — cleanest, set explicitly in your JS
if ($metaApp === 'cv_builder') {
    $target = ($metaType === 'subscription') ? 'cv_subscription' : 'cv_purchase';
} elseif ($metaApp === 'learning_hub') {
    $target = ($metaType === 'community') ? 'lh_community' : 'lh_materials';
}

// (b) Reference prefix routing — fallback if metadata not set
if (!$target) {
    if      (strpos($reference, 'sub_')     === 0) $target = 'cv_subscription';
    elseif  (strpos($reference, 'cv_')      === 0) $target = 'cv_purchase';
    elseif  (strpos($reference, 'lh_com_')  === 0) $target = 'lh_community';
    elseif  (strpos($reference, 'lh_mat_')  === 0) $target = 'lh_materials';
    elseif  (strpos($reference, 'lh_')      === 0) $target = 'lh_materials';
}

// (c) Database lookup — last resort for legacy references with no prefix/metadata
if (!$target) {
    try {
        $cvDb = getCvDB();
        $q    = $cvDb->prepare('SELECT 1 FROM subscriptions WHERE reference=:r LIMIT 1');
        $q->execute([':r' => $reference]);
        if ($q->fetch()) {
            $target = 'cv_subscription';
        } else {
            $q = $cvDb->prepare('SELECT 1 FROM purchases WHERE reference=:r LIMIT 1');
            $q->execute([':r' => $reference]);
            if ($q->fetch()) $target = 'cv_purchase';
        }
    } catch (Exception $e) { /* fall through */ }

    if (!$target) {
        try {
            $lh = new mysqli(LH_DB_HOST, LH_DB_USER, LH_DB_PASS, LH_DB_NAME);
            if (!$lh->connect_error) {
                $stmt = $lh->prepare('SELECT 1 FROM orders WHERE reference=? LIMIT 1');
                $stmt->bind_param('s', $reference);
                $stmt->execute();
                if ($stmt->get_result()->fetch_row()) $target = 'lh_materials';
                $stmt->close();
                $lh->close();
            }
        } catch (Exception $e) { /* fall through */ }
    }
}

if (!$target) {
    // Unknown reference — acknowledge so Paystack stops retrying
    http_response_code(200);
    exit('OK (no target)');
}

// ============================================================
// 3. ROUTE TO FULFILMENT
// ============================================================
try {
    switch ($target) {
        case 'cv_subscription':
            fulfilCvSubscription($reference);
            break;
        case 'cv_purchase':
            fulfilCvPurchase($reference);
            break;
        case 'lh_community':
            fulfilLhCommunity($reference, $customerEm, $data, $LH_COMMUNITY_DAYS);
            break;
        case 'lh_materials':
        default:
            fulfilLhMaterials($reference, $customerEm, $data);
            break;
    }
} catch (Exception $e) {
    error_log('[Ukloole webhook] Error: ' . $e->getMessage());
    http_response_code(500);
    exit('Server error');
}

http_response_code(200);
echo 'OK';

// ============================================================
// HELPERS
// ============================================================
function getCvDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . CV_DB_HOST . ';dbname=' . CV_DB_NAME . ';charset=' . CV_DB_CHARSET;
        $pdo = new PDO($dsn, CV_DB_USER, CV_DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ============================================================
// CV BUILDER — one-time purchase
// ============================================================
function fulfilCvPurchase(string $reference): void {
    $db    = getCvDB();
    $check = $db->prepare('SELECT status FROM purchases WHERE reference=:ref LIMIT 1');
    $check->execute([':ref' => $reference]);
    $row = $check->fetch();
    if ($row && $row['status'] === 'success') return; // already fulfilled

    $token = bin2hex(random_bytes(32));
    $db->prepare(
        "UPDATE purchases
            SET status='success', download_token=:t, updated_at=NOW()
          WHERE reference=:ref AND status='pending'"
    )->execute([':t' => $token, ':ref' => $reference]);
}

// ============================================================
// CV BUILDER — subscription (monthly or yearly)
// ============================================================
function fulfilCvSubscription(string $reference): void {
    $db    = getCvDB();
    $check = $db->prepare('SELECT status, plan FROM subscriptions WHERE reference=:ref LIMIT 1');
    $check->execute([':ref' => $reference]);
    $row = $check->fetch();
    if ($row && $row['status'] === 'active') return; // already activated

    $duration  = (($row['plan'] ?? 'monthly') === 'yearly') ? 365 : 30;
    $startsAt  = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
    $db->prepare(
        "UPDATE subscriptions
            SET status='active', starts_at=:s, expires_at=:e, updated_at=NOW()
          WHERE reference=:ref AND status='pending'"
    )->execute([':s' => $startsAt, ':e' => $expiresAt, ':ref' => $reference]);
}

// ============================================================
// LEARNING HUB — materials / cart purchase
// ============================================================
function fulfilLhMaterials(string $reference, string $email, array $data): void {
    $mysqli = new mysqli(LH_DB_HOST, LH_DB_USER, LH_DB_PASS, LH_DB_NAME);
    if ($mysqli->connect_error) throw new Exception('LH DB: ' . $mysqli->connect_error);
    $mysqli->set_charset('utf8mb4');

    // Idempotency check
    $stmt = $mysqli->prepare('SELECT id, token FROM orders WHERE reference=? LIMIT 1');
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($existing && !empty($existing['token'])) { $mysqli->close(); return; }

    $product  = $data['metadata']['product'] ?? $data['metadata']['cart_summary'] ?? 'bundle';
    $custName = $data['metadata']['name']
              ?? trim(($data['customer']['first_name'] ?? '') . ' ' . ($data['customer']['last_name'] ?? ''));
    $custName = $custName ?: 'Customer';
    $token    = bin2hex(random_bytes(24));
    $expires  = date('Y-m-d H:i:s', time() + 86400); // 24 hours

    if ($existing) {
        $stmt = $mysqli->prepare('UPDATE orders SET token=?, expires_at=? WHERE id=?');
        $stmt->bind_param('ssi', $token, $expires, $existing['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO orders (name, email, product, reference, token, expires_at)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->bind_param('ssssss', $custName, $email, $product, $reference, $token, $expires);
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();

    // Email the download link
    if ($email) {
        $link    = rtrim(LH_SITE_URL, '/') . '/download.php?token=' . $token;
        $subject = 'Your Ukloole Download Link';
        $body    = "Hello {$custName},\n\n"
                 . "Thank you for your purchase!\n\n"
                 . "Click the link below to download your file:\n"
                 . "{$link}\n\n"
                 . "This link expires in 24 hours.\n\n"
                 . "— The Ukloole Team";
        $headers = implode("\r\n", [
            'From: Ukloole <no-reply@ukloole.com>',
            'Reply-To: learn@ukloole.com',
            'Content-Type: text/plain; charset=UTF-8',
        ]);
        @mail($email, $subject, $body, $headers, '-f no-reply@ukloole.com');
    }
}

// ============================================================
// LEARNING HUB — community premium membership
// ============================================================
function fulfilLhCommunity(string $reference, string $email, array $data, int $days): void {
    if (!$email) return;

    $mysqli = new mysqli(LH_DB_HOST, LH_DB_USER, LH_DB_PASS, LH_DB_NAME);
    if ($mysqli->connect_error) throw new Exception('LH DB: ' . $mysqli->connect_error);
    $mysqli->set_charset('utf8mb4');

    $custName = $data['metadata']['name']
              ?? trim(($data['customer']['first_name'] ?? '') . ' ' . ($data['customer']['last_name'] ?? ''));
    $custName = $custName ?: 'Member';
    $plan     = $data['metadata']['plan'] ?? 'Premium';

    // Upsert by email
    $stmt = $mysqli->prepare('SELECT id FROM community_members WHERE email=? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $stmt = $mysqli->prepare(
            "UPDATE community_members
                SET status='active',
                    name = COALESCE(NULLIF(name,''), ?)
              WHERE id=?"
        );
        $stmt->bind_param('si', $custName, $row['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare(
            "INSERT INTO community_members (name, email, status) VALUES (?, ?, 'active')"
        );
        $stmt->bind_param('ss', $custName, $email);
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();

    // Welcome email with setup link
    $setupLink = rtrim(LH_SITE_URL, '/') . '/community-login.php?setup=1';
    $subject   = 'Welcome to Ukloole Premium Community';
    $body      = "Hello {$custName},\n\n"
               . "Your {$plan} premium community access is now active!\n\n"
               . "Set up your password and log in here:\n"
               . "{$setupLink}\n\n"
               . "Use the same email address you paid with: {$email}\n\n"
               . "Your access lasts {$days} days.\n\n"
               . "— The Ukloole Team";
    $headers   = implode("\r\n", [
        'From: Ukloole <no-reply@ukloole.com>',
        'Reply-To: learn@ukloole.com',
        'Content-Type: text/plain; charset=UTF-8',
    ]);
    @mail($email, $subject, $body, $headers, '-f no-reply@ukloole.com');
}
