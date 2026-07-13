<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Contact Us';

$quoteMsg = $ticketMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'quote') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name && $email && $message) {
            $stmt = db()->prepare("INSERT INTO quotes (name,email,company,service,message) VALUES (?,?,?,?,?)");
            $stmt->execute([$name,$email,$company,$service,$message]);
            $quoteMsg = 'success';
        } else { $quoteMsg = 'error'; }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'ticket') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name && $email && $subject && $message) {
            $stmt = db()->prepare("INSERT INTO tickets (name,email,subject,message) VALUES (?,?,?,?)");
            $stmt->execute([$name,$email,$subject,$message]);
            $ticketMsg = 'success';
        } else { $ticketMsg = 'error'; }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<section style="background:#0f172a;color:#fff;padding:10rem 0 6rem;position:relative;overflow:hidden;border-bottom:1px solid rgba(20,184,166,.2);">
  <div style="position:absolute;top:0;right:0;width:600px;height:600px;background:rgba(99,102,241,.10);border-radius:50%;filter:blur(80px);transform:translate(30%,-50%);pointer-events:none;"></div>
  <div class="container text-center position-relative" style="z-index:2;max-width:860px;">
    <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:1.25rem;line-height:1.15;">
      Contact <span style="color:#14b8a6;">Us</span>
    </h1>
    <p style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.75);max-width:600px;margin:0 auto;line-height:1.7;">
      Ready to transform your customer service? Let's talk.
    </p>
  </div>
</section>

<section class="py-5" style="background:#f8fafc">
    <div class="container">
        <div class="row g-5">
            <!-- Quote Form -->
            <div class="col-lg-6">
                <div class="service-card">
                    <h3 class="mb-4"><i class="bi bi-file-text me-2" style="color:#6366f1"></i>Request a Quote</h3>
                    <?php if($quoteMsg==='success'): ?><div class="alert alert-success">✅ Quote request sent! We'll get back to you within 24 hours.</div><?php endif; ?>
                    <?php if($quoteMsg==='error'): ?><div class="alert alert-danger">Please fill in all required fields.</div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="form_type" value="quote">
                        <div class="mb-3"><label class="form-label fw-600">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-600">Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-600">Company</label><input type="text" name="company" class="form-control"></div>
                        <div class="mb-3"><label class="form-label fw-600">Service Interested In</label>
                            <select name="service" class="form-select">
                                <option value="">Select a service...</option>
                                <option>Remote Customer Service Agents</option>
                                <option>Call Centre Outsourcing</option>
                                <option>Live-Chat & Helpdesk Support</option>
                                <option>AI Automation</option>
                                <option>Business Tech Setup</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label fw-600">Message *</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
                        <button type="submit" class="btn btn-primary-custom w-100">Send Quote Request <i class="bi bi-arrow-right ms-2"></i></button>
                    </form>
                </div>
            </div>
            <!-- Ticket Form -->
            <div class="col-lg-6">
                <div class="service-card">
                    <h3 class="mb-4"><i class="bi bi-ticket me-2" style="color:#6366f1"></i>Support Ticket</h3>
                    <?php if($ticketMsg==='success'): ?><div class="alert alert-success">✅ Ticket submitted! We'll respond within 4 hours.</div><?php endif; ?>
                    <?php if($ticketMsg==='error'): ?><div class="alert alert-danger">Please fill in all required fields.</div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="form_type" value="ticket">
                        <div class="mb-3"><label class="form-label fw-600">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-600">Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-600">Subject *</label><input type="text" name="subject" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-600">Message *</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
                        <button type="submit" class="btn btn-primary-custom w-100">Submit Ticket <i class="bi bi-arrow-right ms-2"></i></button>
                    </form>
                </div>
                <div class="mt-4 p-4" style="background:white;border-radius:20px;box-shadow:0 4px 15px rgba(0,0,0,.08)">
                    <h5 class="fw-bold mb-3">Other Ways to Reach Us</h5>
                    <p><i class="bi bi-envelope-fill me-2" style="color:#6366f1"></i><a href="mailto:info@ukloole.com">info@ukloole.com</a></p>
                    <p><i class="bi bi-whatsapp me-2" style="color:#25d366"></i><a href="https://wa.me/2348101593648" target="_blank">+234 810 159 3648</a></p>
                    <p class="mb-0"><i class="bi bi-calendar me-2" style="color:#6366f1"></i><a  href="javascript:void(0)" onclick="openCal()">Book a Strategy Call</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Calendly Modal -->
<div id="cal-modal" onclick="if(event.target===this)closeCal()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:16px;width:92%;max-width:680px;height:85vh;position:relative;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,.3);">
    <button onclick="closeCal()" style="position:absolute;top:10px;right:14px;background:none;border:none;font-size:1.8rem;line-height:1;cursor:pointer;color:#64748b;z-index:2;">&times;</button>
    <iframe id="cal-frame" src="" width="100%" height="100%" frameborder="0"></iframe>
  </div>
</div>
<script>
function openCal(){
  var m=document.getElementById('cal-modal');
  document.getElementById('cal-frame').src='https://calendly.com/admin-ukloole/ukloole-early-access';
  m.style.display='flex';
  document.body.style.overflow='hidden';
}
function closeCal(){
  document.getElementById('cal-modal').style.display='none';
  document.getElementById('cal-frame').src='';
  document.body.style.overflow='';
}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeCal();});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>