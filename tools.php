<?php
// TEMP DIAGNOSTIC — remove after debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
echo "<!-- DEBUG-MARKER-V2 -->";

define('UKLOOLE', true);
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Tools';

// Add pricing & best_for columns if they don't exist yet (safe migration)
try {
  db()->exec("ALTER TABLE tools ADD COLUMN pricing varchar(255) DEFAULT NULL");
} catch(Exception $e) {}
try {
  db()->exec("ALTER TABLE tools ADD COLUMN best_for varchar(255) DEFAULT NULL");
} catch(Exception $e) {}
try {
  db()->exec("ALTER TABLE tools ADD COLUMN rating decimal(2,1) DEFAULT 4.5");
} catch(Exception $e) {}

// Fetch all tools grouped by category
$rows = db()->query("SELECT * FROM tools ORDER BY category ASC, featured DESC, name ASC")->fetchAll();
$categories = [];
foreach($rows as $t) {
  $cat = $t['category'] ?: 'General';
  $categories[$cat][] = $t;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section style="background:#0f172a;color:#fff;padding:10rem 0 6rem;position:relative;overflow:hidden;border-bottom:1px solid rgba(20,184,166,.2);">
  <div style="position:absolute;top:0;right:0;width:700px;height:700px;background:rgba(99,102,241,.10);border-radius:50%;filter:blur(80px);transform:translate(30%,-50%);pointer-events:none;"></div>
  <div class="container text-center position-relative" style="z-index:2;max-width:860px;">
    <h1 style="font-size:clamp(2rem,5vw,3.75rem);font-weight:800;margin-bottom:1.25rem;line-height:1.1;">
      Find the Best Tools <span style="color:#14b8a6;">for Your Business</span>
    </h1>
    <p style="font-size:clamp(1rem,2vw,1.2rem);color:rgba(255,255,255,.75);max-width:640px;margin:0 auto;line-height:1.75;">
      Don't waste time and money on software that wasn't built with your business in mind. Get personalised recommendations.
    </p>
  </div>
</section>

<!-- ===== WHY THIS LIST MATTERS BANNER ===== -->
<section style="position:relative;z-index:20;margin-top:-2.5rem;margin-bottom:4rem;">
  <div class="container" style="max-width:960px;">
    <div style="background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.10);border:1px solid #f1f5f9;padding:2.5rem 3rem;display:flex;flex-wrap:wrap;gap:2rem;align-items:center;">
      <div style="flex:1;min-width:240px;">
        <h3 style="font-family:Poppins,sans-serif;font-weight:700;font-size:1.15rem;color:#0f172a;margin-bottom:.6rem;">Why this list matters</h3>
        <p style="color:#64748b;font-size:.97rem;line-height:1.7;margin:0;">Many popular tools are priced in USD for Western markets. We research tools that work for your business, your industry, and your currency. We do the research so you don't have to.</p>
      </div>
      <div style="flex:1;min-width:240px;background:#f8fafc;border-radius:14px;border:1px solid #e2e8f0;padding:1.5rem;">
        <h4 style="font-family:Poppins,sans-serif;font-weight:700;color:#6366f1;margin-bottom:.5rem;font-size:1rem;">
          <i class="bi bi-calendar3 me-2"></i>Need a custom stack?
        </h4>
        <p style="color:#64748b;font-size:.9rem;margin-bottom:1rem;line-height:1.65;">Schedule a <strong>free</strong> call — we'll build and set it up for you.</p>
        <a style="display:block;text-align:center;background:#6366f1;color:#fff;border-radius:10px;padding:.75rem 1rem;font-weight:700;text-decoration:none;font-size:.95rem;"
           onmouseover="this.style.background='#4f46e5'" onmouseout="this.style.background='#6366f1'" href="javascript:void(0)" onclick="openCal()">
          Schedule a Free Call
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===== TOOL CATEGORIES ===== -->
<?php if(empty($categories)): ?>
<section class="py-5" style="background:#f8fafc;">
  <div class="container text-center py-5">
    <i class="bi bi-tools" style="font-size:4rem;color:#cbd5e1;"></i>
    <h3 class="mt-3" style="color:#64748b;">Tools coming soon</h3>
    <p style="color:#94a3b8;">Check back soon — we're curating the best tools  that work across borders, industries, and currencies.</p>
  </div>
</section>
<?php else: ?>
<section style="padding-bottom:5rem;background:#f8fafc;">
  <div class="container" style="max-width:1100px;">
    <?php foreach($categories as $catName => $tools): ?>
    <div style="margin-bottom:5rem;padding-top:3rem;">
      <div style="margin-bottom:2rem;">
        <h2 style="font-family:Poppins,sans-serif;font-weight:700;font-size:1.85rem;color:#0f172a;margin-bottom:.4rem;"><?= h($catName) ?></h2>
      </div>

      <div class="row g-4">
        <?php foreach($tools as $t): ?>
        <div class="col-md-6 col-lg-4">
          <div style="background:#fff;border-radius:20px;padding:1.75rem;box-shadow:0 2px 12px rgba(0,0,0,.06);border:1.5px solid <?= $t['featured'] ? '#14b8a6' : '#e2e8f0' ?>;display:flex;flex-direction:column;height:100%;position:relative;transition:box-shadow .2s;"
               onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.12)'" onmouseout="this.style.boxShadow='0 2px 12px rgba(0,0,0,.06)'">

            <?php if($t['featured']): ?>
            <div style="position:absolute;top:-12px;right:-8px;background:#14b8a6;color:#fff;font-size:.75rem;font-weight:700;padding:4px 12px;border-radius:50px;box-shadow:0 2px 8px rgba(20,184,166,.35);display:flex;align-items:center;gap:4px;">
              <i class="bi bi-star-fill" style="font-size:.7rem;"></i> Ukloole Pick
            </div>
            <?php endif; ?>

            <!-- Icon + Name + Stars -->
            <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;">
              <div style="width:56px;height:56px;background:#f8fafc;border-radius:14px;border:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                <?php if(!empty($t['icon']) && substr(trim($t['icon']), 0, 1) === '<'): ?>
                  <?= $t['icon'] ?>
                <?php elseif(!empty($t['icon'])): ?>
                  <img src="<?= h($t['icon']) ?>" alt="" style="width:36px;height:36px;object-fit:contain;">
                <?php else: ?>
                  <span style="font-family:Poppins,sans-serif;font-weight:800;font-size:1.3rem;color:#6366f1;"><?= strtoupper(substr($t['name'],0,1)) ?></span>
                <?php endif; ?>
              </div>
              <div>
                <h3 style="font-family:Poppins,sans-serif;font-weight:700;font-size:1rem;color:#0f172a;margin-bottom:.25rem;"><?= h($t['name']) ?></h3>
                <?php if(!empty($t['rating'])): ?>
                <div style="display:flex;align-items:center;gap:2px;">
                  <?php $r = (float)$t['rating']; for($s=1;$s<=5;$s++): $f=$s<=floor($r); ?>
                  <i class="bi bi-star<?= $f?'-fill':'' ?>" style="font-size:.75rem;color:<?= $f?'#f59e0b':'#cbd5e1' ?>;"></i>
                  <?php endfor; ?>
                  <span style="font-size:.78rem;color:#64748b;margin-left:4px;font-weight:600;"><?= $r ?></span>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Pricing & Best For -->
            <div style="margin-bottom:1rem;flex:1;">
              <?php if(!empty($t['pricing'])): ?>
              <div style="display:flex;gap:.5rem;font-size:.88rem;margin-bottom:.4rem;">
                <span style="font-weight:700;color:#334155;min-width:68px;">Pricing:</span>
                <span style="color:#64748b;"><?= h($t['pricing']) ?></span>
              </div>
              <?php endif; ?>
              <?php if(!empty($t['best_for'])): ?>
              <div style="display:flex;gap:.5rem;font-size:.88rem;margin-bottom:.9rem;">
                <span style="font-weight:700;color:#334155;min-width:68px;">Best for:</span>
                <span style="color:#64748b;"><?= h($t['best_for']) ?></span>
              </div>
              <?php endif; ?>
              <?php if(!empty($t['description'])): ?>
              <p style="font-size:.9rem;color:#64748b;line-height:1.65;margin:0;"><?= h($t['description']) ?></p>
              <?php endif; ?>
            </div>

            <!-- CTA -->
            <a href="<?= h($t['url']) ?>" target="_blank" rel="noopener noreferrer"
               style="display:block;text-align:center;border:1.5px solid rgba(99,102,241,.3);color:#6366f1;border-radius:10px;padding:.65rem 1rem;font-weight:600;font-size:.9rem;text-decoration:none;transition:background .2s;margin-top:auto;"
               onmouseover="this.style.background='rgba(99,102,241,.06)'" onmouseout="this.style.background='transparent'">
              Visit Site <i class="bi bi-arrow-up-right ms-1"></i>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===== BOTTOM CTA ===== -->
<section style="background:#0f172a;color:#fff;padding:5rem 0;text-align:center;">
  <div class="container" style="max-width:780px;">
    <h2 style="font-family:Poppins,sans-serif;font-weight:800;font-size:clamp(1.75rem,4vw,2.75rem);margin-bottom:1rem;">Need help choosing and setting up?</h2>
    <p style="color:#94a3b8;font-size:1.1rem;margin-bottom:.5rem;">We don't just recommend tools. We implement them, integrate them, and train your team.</p>
    <p style="color:#14b8a6;font-weight:700;font-size:.85rem;letter-spacing:.1em;text-transform:uppercase;margin-bottom:2.5rem;">The call is completely free</p>
    <a class="btn btn-hero btn-lg" style="background:#14b8a6;color:#fff;border:none;border-radius:50px;padding:1rem 3rem;font-size:1.1rem;" href="javascript:void(0)" onclick="openCal()">
      Schedule a Free Call <i class="bi bi-arrow-right ms-2"></i>
    </a>
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