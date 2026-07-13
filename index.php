<?php
define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';

// Handle newsletter subscribe
$subMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe_email'])) {
    $email = filter_var(trim($_POST['subscribe_email']), FILTER_VALIDATE_EMAIL);
    if ($email) {
        try {
            $stmt = db()->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            $subMsg = 'success';
        } catch (Exception $e) { $subMsg = 'error'; }
    } else { $subMsg = 'invalid'; }
}

// Load published testimonials
$testimonials = db()->query("SELECT * FROM testimonials WHERE published=1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero-section" id="home" style="background-image:url('/assets/images/pixabay.jpg');background-size:cover;background-position:center;background-repeat:no-repeat;">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-7 hero-content pb-5">
                <div class="badge-premium mt-4"><i class="bi bi-stars me-2"></i>Now Onboarding</div>
                <h1 class="hero-title mb-4">
                    Tech and Talent For The <span class="gradient-text">Modern Business</span>
                </h1>
                <p class="hero-subtitle mb-5">Get a complete customer service unit without hiring, training, or managing anyone. Ukloole handles your WhatsApp, email, and calls so you can focus on growing your business.</p>
                <div class="hero-cta d-flex flex-wrap gap-3">
                    <a class="btn btn-hero btn-lg" href="javascript:void(0)" onclick="openCal()">Book a Call <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="/services.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Explore Services</a>
                </div>
                
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-illustration">
                    <div class="floating-card card-1"><i class="bi bi-headset"></i><span>24/7 Support</span></div>
                    <div class="floating-card card-2"><i class="bi bi-graph-up-arrow"></i><span>Boost Revenue</span></div>
                    <div class="floating-card card-3"><i class="bi bi-robot"></i><span>AI-Powered</span></div>
                    <div class="floating-card card-4"><i class="bi bi-people"></i><span>Expert Team</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="scroll-indicator"><i class="bi bi-chevron-down"></i></div>
</section>

<!-- PAIN POINTS -->
<section class="pain-section py-5">
    <div class="container">
        <div class="row text-center mb-5"><div class="col-lg-8 mx-auto">
            <h2 class="section-title mb-4">Does This Sound Familiar?</h2>
            <p class="section-subtitle">You're not alone. Thousands of business owners face these challenges every day.</p>
        </div></div>
        <div class="row g-4 mb-5">
            <?php foreach([
                ['bi-clock-history','"Support is draining my time."'],
                ['bi-person-x','"I can\'t find reliable reps."'],
                ['bi-chat-left-dots','"I\'m losing customers with slow replies."'],
                ['bi-currency-dollar','"Outsourcing is too expensive."'],
            ] as [$icon,$quote]): ?>
            <div class="col-md-6 col-lg-3">
                <div class="pain-card">
                    <div class="pain-icon"><i class="bi <?= $icon ?>"></i></div>
                    <p class="pain-quote"><?= $quote ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="solution-banner text-center">
            <h3>You don't have to struggle with this anymore.</h3>
            <p class="mb-0">Ukloole gives you a complete customer service solution without the headaches.</p>
        </div>
    </div>
</section>

<!-- SERVICES OVERVIEW -->
<section class="services-section py-5">
    <div class="container">
        <div class="row text-center mb-5"><div class="col-lg-8 mx-auto">
            <h2 class="section-title mb-4">Comprehensive Customer Service Solutions</h2>
            <p class="section-subtitle">Choose the service that fits your business or combine them for complete coverage.</p>
        </div></div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4"><div class="service-card"><div class="service-icon"><i class="bi bi-headset"></i></div><h3>Remote Customer Service Agents</h3><p>Trained agents plug into your systems and represent your brand seamlessly, 24/7.</p><ul class="service-features"><li><i class="bi bi-check-circle-fill"></i>Instant integration</li><li><i class="bi bi-check-circle-fill"></i>Brand voice training</li></ul></div></div>
            <div class="col-md-6 col-lg-4"><div class="service-card featured"><span class="featured-badge"><i class="bi bi-stars me-1"></i>Popular</span><div class="service-icon"><i class="bi bi-robot"></i></div><h3>AI Automation</h3><p>AI-powered agents provide fast, accurate, round-the-clock support with smart escalation.</p><ul class="service-features"><li><i class="bi bi-check-circle-fill"></i>24/7 availability</li><li><i class="bi bi-check-circle-fill"></i>Instant responses</li></ul></div></div>
            <div class="col-md-6 col-lg-4"><div class="service-card"><div class="service-icon"><i class="bi bi-chat-dots"></i></div><h3>Live-Chat & Helpdesk</h3><p>Real-time assistance via chat, email, and phone ensuring fast responses every time.</p><ul class="service-features"><li><i class="bi bi-check-circle-fill"></i>Multi-channel</li><li><i class="bi bi-check-circle-fill"></i>Lightning-fast responses</li></ul></div></div>
        </div>
        <div class="text-center mt-5"><a href="/services.php" class="btn btn-primary-custom btn-lg">View All Services <i class="bi bi-arrow-right ms-2"></i></a></div>
    </div>
</section>

<!-- BENEFITS -->
<section class="benefits-section py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <h2 class="section-title mb-4">What Ukloole Will Do for You</h2>
                <p class="section-subtitle mb-4">Transform your customer service from a headache into your competitive advantage.</p>
                <a class="btn btn-primary-custom btn-lg" href="javascript:void(0)" onclick="openCal()">Get Started Today <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php foreach([
                        ['bi-mortarboard','Trained Support Team','Full team trained for your business'],
                        ['bi-grid','Omnichannel Support','WhatsApp, email, chat & calls'],
                        ['bi-heart','Human Touch','Personalized, genuine responses'],
                        ['bi-lightning','AI-Enhanced','Backed by powerful automation'],
                        ['bi-building','Zero Overhead','No office or equipment costs'],
                        ['bi-person-check','No Hiring Hassles','Skip recruitment and training stress'],
                        ['bi-speedometer','Quick Deployment','Up and running fast'],
                        ['bi-credit-card','Predictable Pricing','No hidden costs'],
                    ] as [$icon,$title,$desc]): ?>
                    <div class="col-md-6">
                        <div class="benefit-card">
                            <div class="benefit-icon"><i class="bi <?= $icon ?>"></i></div>
                            <h4><?= $title ?></h4>
                            <p><?= $desc ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHO WE SERVE -->
<section class="who-section py-5">
    <div class="container">
        <div class="row text-center mb-5"><div class="col-lg-8 mx-auto">
            <h2 class="section-title mb-4">Built for Businesses Like Yours</h2>
            <p class="section-subtitle">If customers message you daily, Ukloole will save you time and help you grow.</p>
        </div></div>
        <div class="row g-3">
            <?php foreach([
                ['bi-shop','Small & Medium Businesses'],['bi-cart','Online Stores'],
                ['bi-cloud-check','SaaS Companies'],['bi-truck','Logistics Companies'],
                ['bi-house-door','Real Estate'],['bi-book','Course Creators'],
                ['bi-tools','Service Businesses'],['bi-heart-pulse','Beauty & Wellness'],
            ] as [$icon,$label]): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="industry-badge"><i class="bi <?= $icon ?>"></i><span><?= $label ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($testimonials): ?>
<!-- TESTIMONIALS -->
<section class="py-5" style="background:white;overflow:hidden">
    <div class="container">
        <div class="row text-center mb-5"><div class="col-lg-8 mx-auto">
            <h2 class="section-title mb-4">What Our Clients Say</h2>
            <p class="section-subtitle">Real results from businesses that trusted Ukloole.</p>
        </div></div>
    </div>
    <!-- Marquee track -->
    <div class="ukloole-marquee-wrapper">
        <div class="ukloole-marquee-track">
            <?php foreach([$testimonials,$testimonials] as $loop): foreach($loop as $t): ?>
            <div class="ukloole-marquee-card">
                <div class="stars mb-3"><?= str_repeat('<i class="bi bi-star-fill" style="color:#f59e0b"></i>', min(5,(int)($t['rating']??5))) ?></div>
                <p class="testimonial-text">"<?= h($t['testimonial']) ?>"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= strtoupper(substr($t['client_name'],0,1)) ?></div>
                    <div>
                        <div class="author-name"><?= h($t['client_name']) ?></div>
                        <?php if($t['company_name']): ?><div class="author-company"><?= h($t['company_name']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; endforeach; ?>
        </div>
    </div>
</section>
<style>
.ukloole-marquee-wrapper {
    width: 100%;
    overflow: hidden;
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
    mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
}
.ukloole-marquee-track {
    display: flex;
    gap: 24px;
    width: max-content;
    animation: ukloole-scroll 30s linear infinite;
}
.ukloole-marquee-wrapper:hover .ukloole-marquee-track {
    animation-play-state: paused;
}
.ukloole-marquee-card {
    width: 320px;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    transition: box-shadow .2s, transform .2s;
}
.ukloole-marquee-card:hover {
    box-shadow: 0 8px 30px rgba(99,102,241,.15);
    transform: translateY(-3px);
}
@keyframes ukloole-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
<?php endif; ?>

<?php /* FOUNDER SECTION - commented out
<!-- MEET THE FOUNDER -->
<section class="py-5" style="background:#0f172a">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 text-center">
                <div style="width:220px;height:220px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:72px;color:white;font-family:Poppins,sans-serif;font-weight:700;border:4px solid rgba(139,92,246,.4);box-shadow:0 0 60px rgba(139,92,246,.3);margin:0 auto">UK</div>
                <p style="color:rgba(255,255,255,.35);font-size:.8rem;margin-top:12px">Upload your photo to replace this</p>
            </div>
            <div class="col-lg-7">
                <span style="display:inline-flex;align-items:center;background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.3);border-radius:50px;padding:6px 18px;color:#a78bfa;font-weight:600;font-size:.85rem;margin-bottom:20px">Meet the Founder</span>
                <h2 style="font-family:Poppins,sans-serif;font-weight:800;color:white;font-size:clamp(1.75rem,3.5vw,2.5rem);margin-bottom:16px">Built by Someone Who Understands the Struggle</h2>
                <p style="color:rgba(255,255,255,.8);font-size:1.1rem;line-height:1.8;margin-bottom:16px">Ukloole was born from a simple frustration — great businesses failing their customers not because they didn't care, but because they didn't have the right support structure.</p>
                <p style="color:rgba(255,255,255,.65);line-height:1.8;margin-bottom:28px">Our mission is to make world-class customer service accessible to every growing business, regardless of size or budget.</p>
                <a class="btn btn-hero" href="javascript:void(0)" onclick="openCal()">Work With Us <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>
*/ ?>

<!-- CTA -->
<section class="cta-section py-5">
    <div class="container">
        <div class="cta-box">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start mb-4 mb-lg-0">
                    <h2 class="cta-title mb-2">Book Setup Call</h2>
                    <p class="cta-subtitle mb-0">Now Onboarding. Work with Ukloole.</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a class="btn btn-cta btn-lg" href="javascript:void(0)" onclick="openCal()">Get Started <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section style="background:#020617;padding:4rem 0;border-top:1px solid rgba(255,255,255,.06)">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 text-center">
                <h3 style="color:white;font-family:Poppins,sans-serif;font-weight:700;margin-bottom:8px">Stay Ahead</h3>
                <p style="color:rgba(255,255,255,.6);margin-bottom:24px">We break down customer service, AI, and business growth into tips delivered straight to your inbox. No spam, just updates & insights. Join the list!</p>
                <?php if($subMsg==='success'): ?>
                <div class="alert alert-success">🎉 You're subscribed! Welcome to the Ukloole community.</div>
                <?php elseif($subMsg==='error'): ?>
                <div class="alert alert-danger">Something went wrong. Please try again.</div>
                <?php elseif($subMsg==='invalid'): ?>
                <div class="alert alert-warning">Please enter a valid email address.</div>
                <?php endif; ?>
                <form method="post" class="d-flex gap-2">
                    <input type="email" name="subscribe_email" class="form-control rounded-pill newsletter-input" placeholder="Enter your email" required style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:white">
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Subscribe</button>
                </form>
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