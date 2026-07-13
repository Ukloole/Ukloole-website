<?php if(!defined('UKLOOLE')) die(); ?>
<footer class="footer py-4">
    <div class="container">
        <div class="row align-items-start g-4">

            <!-- Logo + tagline — compact left column -->
            <div class="col-lg-3 col-md-4 mb-0">
                <img src="/assets/images/logo.png" alt="Ukloole" style="height:40px !important;width:40px !important;max-width:40px !important;object-fit:contain;display:block;margin-bottom:8px">
                <div style="color:white;font-family:Poppins,sans-serif;font-weight:700;font-size:1.1rem;margin-bottom:10px">Ukloole</div>
                <p class="footer-text" style="font-size:.875rem;line-height:1.7;margin:0">Tech and Talent For The Modern Business. Complete customer service without the complexity.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-3 col-md-4 mb-0">
                <h6 class="footer-heading mb-2">Quick Links</h6>
                <ul class="footer-links" style="margin:0;font-size:.875rem">
                    <li><a href="/services.php">Services</a></li>
                    <li><a href="/blog.php">Blog</a></li>
                    <li><a href="/tools.php">Free Tools</a></li>
                    <li><a href="/careers.php">Careers</a></li>
                    <li><a href="https://learn.ukloole.com" target="_blank">Learning Hub</a></li>
                    <li><a href="/privacy.php">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-3 col-md-4 mb-0">
                <h6 class="footer-heading mb-2">Contact Us</h6>
                <ul class="footer-links" style="margin:0;font-size:.875rem">
                    <li><i class="bi bi-envelope-fill me-2"></i><a href="mailto:info@ukloole.com">info@ukloole.com</a></li>
                    <li class="mt-2"><i class="bi bi-whatsapp me-2"></i><a href="https://wa.me/2348101593648" target="_blank">+234 810 159 3648</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div class="col-lg-3 col-md-12 mb-0">
                <h6 class="footer-heading mb-2">Follow Us</h6>
                <div class="social-links">
                    <a href="https://www.linkedin.com/company/ukloole/" class="social-link" target="_blank"><i class="bi bi-linkedin"></i></a>
                    <a href="https://www.instagram.com/ukloole/" class="social-link" target="_blank"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.facebook.com/ukloole" class="social-link" target="_blank"><i class="bi bi-facebook"></i></a>
                </div>
            </div>

        </div>
        <hr class="footer-divider my-3">
        <div class="text-center">
            <p class="footer-copyright mb-0">&copy; <?= date('Y') ?> Ukloole. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- WhatsApp Float -->
<a href="https://wa.me/2348101593648" target="_blank" style="position:fixed;bottom:24px;right:24px;width:56px;height:56px;background:#25d366;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:26px;box-shadow:0 4px 20px rgba(37,211,102,.4);z-index:9999;text-decoration:none;transition:all .3s" aria-label="WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
<?php if(!empty($extraScripts)) echo $extraScripts; ?>

</body></html>