<footer>
    <div class="footer-content">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="<?php echo e(asset('logo flower.png')); ?>" alt="CAS Private Care LLC Logo" width="120" height="120">
            </div>
            <p>Connection that cares. Your trusted marketplace for professional caregiving services connecting families with verified care professionals.</p>
            <p style="margin-top: 1rem;">We provide a safe, reliable platform where quality care meets convenience. From elderly care to childcare, our verified professionals are ready to support your family's unique needs with compassion and expertise.</p>
            <div class="footer-social">
                <a href="https://www.facebook.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Facebook"><i class="bi bi-facebook"></i></a>
                <a href="https://www.twitter.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Twitter"><i class="bi bi-twitter"></i></a>
                <a href="https://www.instagram.com/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on Instagram"><i class="bi bi-instagram"></i></a>
                <a href="https://www.linkedin.com/company/casprivatecare" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Follow us on LinkedIn"><i class="bi bi-linkedin"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h3>For Clients</h3>
            <ul>
                <li><a href="<?php echo e(url('/')); ?>#services">Browse Services</a></li>
                <li><a href="<?php echo e(url('/')); ?>#how-it-works">How It Works</a></li>
                <li><a href="<?php echo e(url('/register')); ?>">Sign Up</a></li>
                <li><a href="<?php echo e(url('/login')); ?>">Login</a></li>
                <li><a href="<?php echo e(url('/about')); ?>">About</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>For Partners</h3>
            <ul>
                <li><a href="<?php echo e(url('/register')); ?>">Join as Caregiver</a></li>
                <li><a href="<?php echo e(url('/register')); ?>">Marketing Partner</a></li>
                <li><a href="<?php echo e(url('/register')); ?>">Training Center</a></li>
                <li><a href="<?php echo e(url('/')); ?>#how-it-works">How It Works</a></li>
            </ul>
            <h3 style="margin-top: 2rem;">Company</h3>
            <ul>
                <li><a href="<?php echo e(url('/about')); ?>">About Us</a></li>
                <li><a href="<?php echo e(url('/contact')); ?>">Contact</a></li>
                <li><a href="<?php echo e(url('/register')); ?>">Sign Up</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <div class="footer-location">
                <i class="bi bi-geo-alt-fill"></i>
                <span><?php echo e(config('app.address', 'New York, USA')); ?></span>
            </div>
            <div class="footer-location">
                <i class="bi bi-telephone-fill"></i>
                <span><a href="tel:<?php echo e(config('app.phone', '+16462828282')); ?>" style="color: #94a3b8; text-decoration: none;"><?php echo e(config('app.phone', '+1 (646) 282-8282')); ?></a></span>
            </div>
            <div class="footer-location">
                <i class="bi bi-envelope-fill"></i>
                <span><a href="mailto:<?php echo e(config('app.email', 'contact@casprivatecare.online')); ?>" style="color: #94a3b8; text-decoration: none;"><?php echo e(config('app.email', 'contact@casprivatecare.online')); ?></a></span>
            </div>
            <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Newsletter</h3>
            <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 1rem;">Get updates and tips</p>
            <div class="newsletter-input">
                <input type="email" placeholder="Your email">
                <button class="newsletter-btn">Subscribe</button>
            </div>
            <div style="margin-top: 1.5rem;">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387190.27991608967!2d-74.25987368715493!3d40.69767006377258!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sus!4v1234567890" width="100%" height="120" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
    <div class="footer-divider"></div>
    <div class="footer-bottom">
        <p>&copy; 2025 CAS Private Care LLC. All rights reserved.</p>
        <div class="footer-bottom-links">
            <a href="<?php echo e(url('/privacy')); ?>">Privacy Policy</a>
            <a href="<?php echo e(url('/terms')); ?>">Terms of Service</a>
            <a href="<?php echo e(url('/contact')); ?>">Contact</a>
        </div>
    </div>
</footer>

<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/partials/footer.blade.php ENDPATH**/ ?>