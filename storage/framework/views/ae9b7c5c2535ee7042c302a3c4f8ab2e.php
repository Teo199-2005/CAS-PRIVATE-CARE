<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/png" href="<?php echo e(asset('logo flower.png')); ?>">
    
    <!-- Primary Meta Tags -->
    <title>Contact Us | CAS Private Care LLC | Get in Touch</title>
    <meta name="title" content="Contact Us | CAS Private Care LLC | Get in Touch">
    <meta name="description" content="Contact CAS Private Care LLC for questions about our caregiving services, caregiver partnerships, or general inquiries. We're here to help connect families with quality care.">
    <meta name="keywords" content="contact cas private care, caregiver service contact, home care contact, caregiving support">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo e(url('/contact')); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url('/contact')); ?>">
    <meta property="og:title" content="Contact Us | CAS Private Care LLC">
    <meta property="og:description" content="Contact CAS Private Care LLC for questions about our caregiving services or caregiver partnerships.">
    <meta property="og:image" content="<?php echo e(asset('logo flower.png')); ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(url('/contact')); ?>">
    <meta property="twitter:title" content="Contact Us | CAS Private Care LLC">
    <meta property="twitter:description" content="Contact CAS Private Care LLC for questions about our caregiving services or caregiver partnerships.">
    <meta property="twitter:image" content="<?php echo e(asset('logo flower.png')); ?>">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php echo $__env->make('partials.nav-footer-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <style>
        /* CRITICAL: Universal viewport lock */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box !important;
        }

        html {
            overflow-x: hidden !important;
            width: 100vw !important;
            max-width: 100vw !important;
            position: relative;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* CRITICAL: Force ALL block elements to respect viewport */
        div, section, main, header, footer, article, aside {
            max-width: 100vw !important;
            overflow-x: hidden !important;
        }

        main {
            width: 100%;
            overflow-x: hidden;
        }

        .section-light {
            background-color: #ffffff;
            background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
            padding: 6rem 2rem;
        }

        .section-dark {
            background-color: #dbeafe;
            background-image: url("https://www.transparenttextures.com/patterns/dotnoise-light-grey.png");
            padding: 6rem 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .contact-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 8rem 2rem;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: white;
        }

        .contact-hero p {
            font-size: 1.5rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .contact-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .contact-card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .contact-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .contact-card p {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .contact-card a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .contact-card a:hover {
            color: #1e40af;
        }

        /* Contact Form Styles */
        .contact-form {
            background: white;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Sora', sans-serif;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .error-message {
            display: block;
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Sora', sans-serif;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .contact-hero h1 {
                font-size: 2.5rem;
            }

            .contact-hero p {
                font-size: 1.125rem;
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .contact-form {
                padding: 2rem 1.5rem;
            }

            .contact-form > div:first-of-type {
                grid-template-columns: 1fr !important;
            }

            .section-light > .container > div {
                grid-template-columns: 1fr !important;
                gap: 3rem !important;
            }
        }
    </style>
</head>
<body>
    <?php echo $__env->make('partials.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main style="padding-top: 88px;">
        <!-- Contact Section -->
        <section class="section-light">
            <div class="container">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: #1e40af; margin-bottom: 3rem; text-align: center;">Contact Us</h2>
                
                <?php if(session('success')): ?>
                    <div class="alert alert-success" style="background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto;">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-error" style="background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; max-width: 800px; margin-left: auto; margin-right: auto;">
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: start;">
                    <!-- Contact Form -->
                    <div>
                        <h3 style="font-size: 1.75rem; font-weight: 700; color: #1e40af; margin-bottom: 2rem;">Send Us a Message</h3>
                        
                        <form action="<?php echo e(route('contact.submit')); ?>" method="POST" class="contact-form">
                            <?php echo csrf_field(); ?>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="first_name">First Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo e(old('first_name')); ?>" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                                    <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo e(old('last_name')); ?>" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span style="color: #ef4444;">*</span></label>
                                    <input type="tel" id="phone" name="phone" placeholder="(646) 282-8282" value="<?php echo e(old('phone')); ?>" required>
                                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span style="color: #ef4444;">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo e(old('email')); ?>" required>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="service_type">Type of Service <span style="color: #ef4444;">*</span></label>
                                    <select id="service_type" name="service_type" required>
                                        <option value="">Select a service type</option>
                                        <option value="Home Care" <?php echo e(old('service_type') == 'Home Care' ? 'selected' : ''); ?>>Home Care</option>
                                        <option value="Private Duty Nursing" <?php echo e(old('service_type') == 'Private Duty Nursing' ? 'selected' : ''); ?>>Private Duty Nursing</option>
                                        <option value="Care Management" <?php echo e(old('service_type') == 'Care Management' ? 'selected' : ''); ?>>Care Management</option>
                                        <option value="Bedside Care" <?php echo e(old('service_type') == 'Bedside Care' ? 'selected' : ''); ?>>Bedside Care</option>
                                    </select>
                                    <?php $__errorArgs = ['service_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="form-group">
                                    <label for="location">Location where services would be provided <span style="color: #ef4444;">*</span></label>
                                    <select id="location" name="location" required>
                                        <option value="">Select a location</option>
                                        <option value="New York City (Inclusive of five boroughs)" <?php echo e(old('location') == 'New York City (Inclusive of five boroughs)' ? 'selected' : ''); ?>>New York City (Inclusive of five boroughs)</option>
                                        <option value="Long Island" <?php echo e(old('location') == 'Long Island' ? 'selected' : ''); ?>>Long Island</option>
                                        <option value="Westchester" <?php echo e(old('location') == 'Westchester' ? 'selected' : ''); ?>>Westchester</option>
                                        <option value="New Jersey" <?php echo e(old('location') == 'New Jersey' ? 'selected' : ''); ?>>New Jersey</option>
                                        <option value="Connecticut" <?php echo e(old('location') == 'Connecticut' ? 'selected' : ''); ?>>Connecticut</option>
                                    </select>
                                    <?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <span class="error-message"><?php echo e($message); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="additional_info">Additional Information</label>
                                <textarea id="additional_info" name="additional_info" rows="5" placeholder="Tell us more about your needs..."><?php echo e(old('additional_info')); ?></textarea>
                                <?php $__errorArgs = ['additional_info'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="error-message"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label style="display: flex; align-items: center; cursor: pointer; font-weight: 400;">
                                    <input type="checkbox" name="newsletter" value="1" <?php echo e(old('newsletter') ? 'checked' : ''); ?> style="width: auto; margin-right: 0.75rem; cursor: pointer;">
                                    <span>Subscribe to our Newsletter</span>
                                </label>
                            </div>

                            <div style="text-align: center;">
                                <button type="submit" class="submit-btn">Send Message</button>
                            </div>
                        </form>
                    </div>

                    <!-- Contact Information Cards -->
                    <div>
                        <h3 style="font-size: 1.75rem; font-weight: 700; color: #1e40af; margin-bottom: 2rem;">Get in Touch</h3>
                        <div class="contact-info" style="grid-template-columns: 1fr; gap: 1.5rem; margin-top: 0;">
                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <h3>Email Us</h3>
                                <p>Send us an email anytime</p>
                                <a href="mailto:contact@casprivatecare.online">contact@casprivatecare.online</a>
                            </div>

                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <h3>Call Us</h3>
                                <p>Mon-Fri 9am-6pm EST</p>
                                <a href="tel:+16462828282">+1 (646) 282-8282</a>
                            </div>

                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <h3>Location</h3>
                                <p>Service Area</p>
                                <p style="color: #1e40af; font-weight: 600;">New York, USA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        // CRITICAL: Force viewport reset and prevent horizontal scroll
        (function() {
            // Reset scroll position to left on load
            window.scrollTo(0, 0);
            document.documentElement.scrollLeft = 0;
            document.body.scrollLeft = 0;
            
            // Force all elements to respect viewport
            function constrainViewport() {
                const html = document.documentElement;
                const body = document.body;
                
                html.style.overflowX = 'hidden';
                html.style.maxWidth = '100vw';
                html.style.width = '100%';
                
                body.style.overflowX = 'hidden';
                body.style.maxWidth = '100vw';
                body.style.width = '100%';
                
                // Reset horizontal scroll
                window.scrollTo(0, window.scrollY);
                html.scrollLeft = 0;
                body.scrollLeft = 0;
            }
            
            // Run on load
            constrainViewport();
            
            // Run after DOM is fully loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', constrainViewport);
            }
            
            // Run on window load
            window.addEventListener('load', constrainViewport);
            
            // Prevent horizontal scrolling
            window.addEventListener('scroll', function() {
                if (window.scrollX !== 0) {
                    window.scrollTo(0, window.scrollY);
                }
            });
            
            // Reset on resize
            window.addEventListener('resize', constrainViewport);
        })();
    </script>

    <script>
        // Phone number formatting
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 0) {
                        if (value.length <= 3) {
                            value = '(' + value;
                        } else if (value.length <= 6) {
                            value = '(' + value.slice(0, 3) + ') ' + value.slice(3);
                        } else {
                            value = '(' + value.slice(0, 3) + ') ' + value.slice(3, 6) + '-' + value.slice(6, 10);
                        }
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>

<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/contact.blade.php ENDPATH**/ ?>