<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Services';
require_once __DIR__ . '/includes/header.php';

$services = [
    [
        'icon'     => 'bi-headset',
        'title'    => 'Remote Customer Service Agents',
        'badge'    => null,
        'featured' => false,
        'desc'     => 'Already have your own CRM or support tools? Our trained agents plug directly into your systems and start working immediately, adapting quickly and representing your brand voice seamlessly.',
        'features' => ['Instant integration', 'Brand voice training', 'Your tools, our expertise'],
    ],
    [
        'icon'     => 'bi-telephone',
        'title'    => 'Call Centre Outsourcing',
        'badge'    => 'On Request',
        'featured' => false,
        'desc'     => 'We handle everything from setup to daily operations. Ukloole provides CRM tools, onboards your business, and assigns trained agents who manage customer interactions with care and professionalism.',
        'features' => ['Complete setup included', 'CRM tools provided', 'Professional agents'],
    ],
    [
        'icon'     => 'bi-chat-dots',
        'title'    => 'Live-Chat &amp; Helpdesk Support',
        'badge'    => null,
        'featured' => false,
        'desc'     => 'We offer real-time assistance via chat, email, and phone ensuring fast responses, efficient resolutions, and satisfied customers every time.',
        'features' => ['Multi-channel support', 'Lightning-fast responses', 'Efficient problem solving'],
    ],
    [
        'icon'     => 'bi-robot',
        'title'    => 'AI Automation',
        'badge'    => '⭐ Popular',
        'featured' => true,
        'desc'     => "Ukloole's AI-powered customer service agents provide fast, accurate, and round-the-clock support for your business. They handle inquiries, resolve common issues, route complex requests to human agents, and deliver personalized experiences in real time.",
        'features' => ['24/7 availability', 'Instant responses', 'Smart escalation'],
    ],
    [
        'icon'     => 'bi-gear',
        'title'    => 'Business Tech Setup',
        'badge'    => null,
        'featured' => false,
        'desc'     => 'Running a business with no systems in place? Ukloole sets up your complete business tech stack (website, CRM, etc) so operations are clear, structured, and ready to scale.',
        'features' => ['Onboarding and system setup', 'Essential business tools', 'Clean, scalable operations'],
    ],
];
?>

<section style="background:#0f172a;color:#fff;padding:10rem 0 6rem;position:relative;overflow:hidden;border-bottom:1px solid rgba(20,184,166,.2);">
  <div style="position:absolute;top:0;right:0;width:600px;height:600px;background:rgba(99,102,241,.10);border-radius:50%;filter:blur(80px);transform:translate(30%,-50%);pointer-events:none;"></div>
  <div class="container text-center position-relative" style="z-index:2;max-width:860px;">
    <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:1.25rem;line-height:1.15;">
      Our <span style="color:#14b8a6;">Services</span>
    </h1>
    <p style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.75);max-width:600px;margin:0 auto 2rem;line-height:1.7;">
      Complete customer service solutions tailored to your business needs.
    </p>
    <a href="/contact.php" class="btn btn-hero btn-lg">Get a Quote <i class="bi bi-arrow-right ms-2"></i></a>
  </div>
</section>

<section class="services-section py-5">
    <div class="container">
        <div class="row g-4">
        <?php foreach($services as $s): ?>
            <div class="col-md-6 col-lg-4">
                <div class="service-card <?= $s['featured'] ? 'featured' : '' ?> h-100">
                    <?php if($s['badge']): ?>
                        <span class="<?= $s['featured'] ? 'featured-badge' : 'request-badge' ?>"><?= $s['badge'] ?></span>
                    <?php endif; ?>
                    <div class="service-icon"><i class="bi <?= $s['icon'] ?>"></i></div>
                    <h3><?= $s['title'] ?></h3>
                    <p><?= $s['desc'] ?></p>
                    <ul class="service-features">
                        <?php foreach($s['features'] as $f): ?>
                            <li><i class="bi bi-check-circle-fill"></i><?= $f ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="text-center mt-5 py-4">
            <h3 style="font-family:Poppins,sans-serif;font-weight:700;margin-bottom:16px">Ready to get started?</h3>
            <a class="btn btn-primary-custom btn-lg me-3" href="javascript:void(0)" onclick="openCal()">Book a call</a>
            <a href="/contact.php" class="btn btn-outline-secondary btn-lg">Request a Quote</a>
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