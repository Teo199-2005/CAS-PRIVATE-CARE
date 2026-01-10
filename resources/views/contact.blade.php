<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Contact Us - CAS Private Care LLC | Get in Touch</title>
    <meta name="title" content="Contact Us - CAS Private Care LLC | Get in Touch">
    <meta name="description" content="Contact CAS Private Care LLC for professional caregiving services in New York. Call us at (646) 282-8282 or email contact@casprivatecare.online. We're here to help!">
    <meta name="keywords" content="contact CAS Private Care, caregiving inquiries, New York care services, customer support">
    <meta name="author" content="CAS Private Care LLC">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/contact') }}">
    <meta property="og:title" content="Contact Us - CAS Private Care LLC">
    <meta property="og:description" content="Get in touch with CAS Private Care LLC for professional caregiving services in New York.">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/contact') }}">
    <meta property="twitter:title" content="Contact Us - CAS Private Care LLC">
    <meta property="twitter:description" content="Get in touch with CAS Private Care LLC for professional caregiving services in New York.">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    @include('partials.nav-footer-styles')
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #0B4FA2;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: #f8fafc;
        }

        /* Contact Hero Section */
        .contact-hero {
            margin-top: 88px;
            padding: 4rem 2rem 6rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .contact-hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease;
        }

        .contact-hero p {
            font-size: 1.25rem;
            color: #e0f2fe;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Main Contact Section */
        .contact-section {
            padding: 0;
            position: relative;
            margin-top: -3rem;
        }

        .contact-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease 0.4s both;
        }

        /* Contact Form Side */
        .contact-form-side {
            padding: 4rem;
        }

        .contact-form-side h2 {
            font-family: 'Sora', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e40af;
        }

        .contact-form-side h2 span {
            color: #f97316;
        }

        .contact-form-side > p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group label span {
            color: #ef4444;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .form-checkbox label {
            margin: 0;
            font-weight: 400;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 1.25rem 2rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Contact Info Side */
        .contact-info-side {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .contact-info-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .contact-info-side h3 {
            font-family: 'Sora', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .contact-info-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .contact-info-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .contact-info-item i {
            font-size: 2rem;
            color: #f97316;
            margin-bottom: 1rem;
            display: block;
        }

        .contact-info-item h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .contact-info-item p {
            color: #e0f2fe;
            margin: 0;
            font-size: 1rem;
        }

        .contact-info-item a {
            color: #e0f2fe;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .contact-info-item a:hover {
            color: white;
        }

        /* Success Message */
        .success-message {
            display: none;
            padding: 1.25rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            animation: slideDown 0.5s ease;
        }

        .success-message i {
            margin-right: 0.5rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading State */
        .submit-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .submit-btn.loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .contact-info-side {
                order: -1;
                padding: 3rem 2rem;
            }

            .contact-form-side {
                padding: 3rem 2rem;
            }
        }

        @media (max-width: 768px) {
            .contact-hero {
                padding: 3rem 1.5rem 4rem;
            }

            .contact-hero h1 {
                font-size: 2.5rem;
            }

            .contact-hero p {
                font-size: 1.1rem;
            }

            .contact-form-side h2 {
                font-size: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .contact-form-side,
            .contact-info-side {
                padding: 2.5rem 1.5rem;
            }

            .contact-info-side h3 {
                font-size: 1.75rem;
            }

            .contact-container {
                padding: 0 1rem;
            }
        }

        @media (max-width: 480px) {
            .contact-hero h1 {
                font-size: 2rem;
            }

            .contact-hero p {
                font-size: 1rem;
            }

            .contact-form-side h2 {
                font-size: 1.75rem;
            }

            .contact-grid {
                border-radius: 20px;
            }
        }

        /* Bottom spacing */
        .contact-spacer {
            padding: 4rem 0;
        }
    </style>
</head>
<body>
    @include('partials.navigation')

    <main>
        <!-- Hero Section -->
        <section class="contact-hero">
            <h1><span style="color: #f97316;">Contact</span> <span style="color: white;">Us</span></h1>
            <p>Have questions? We're here to help! Reach out and let's discuss how we can support your care needs.</p>
        </section>

        <!-- Main Contact Section -->
        <section class="contact-section">
            <div class="contact-container">
                <div class="contact-grid">
                    <!-- Contact Form -->
                    <div class="contact-form-side">
                        <h2><span>Send Us</span> a Message</h2>
                        <p>Fill out the form below and we'll get back to you within 24 hours.</p>

                        <div class="success-message" id="successMessage">
                            <i class="bi bi-check-circle-fill"></i>
                            Thank you! Your message has been sent successfully.
                        </div>

                        <form id="contactForm" action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name <span>*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="form-input" placeholder="John" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name <span>*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Doe" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span>*</span></label>
                                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="(646) 282-8282" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email <span>*</span></label>
                                    <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="service_type">Type of Service <span>*</span></label>
                                    <select id="service_type" name="service_type" class="form-select" required>
                                        <option value="">Select a service type</option>
                                        <option value="elderly-care">Elderly Care</option>
                                        <option value="companion-care">Companion Care</option>
                                        <option value="personal-care">Personal Care</option>
                                        <option value="special-needs">Special Needs Care</option>
                                        <option value="training">Training Center</option>
                                        <option value="partner">Become a Partner</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="location">Location <span>*</span></label>
                                    <select id="location" name="location" class="form-select" required>
                                        <option value="">Select a location</option>
                                        <option value="manhattan">Manhattan</option>
                                        <option value="brooklyn">Brooklyn</option>
                                        <option value="queens">Queens</option>
                                        <option value="bronx">Bronx</option>
                                        <option value="staten-island">Staten Island</option>
                                        <option value="other">Other NY Area</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="message">Additional Information</label>
                                <textarea id="message" name="message" class="form-textarea" placeholder="Tell us more about your needs..."></textarea>
                            </div>

                            <div class="form-checkbox">
                                <input type="checkbox" id="newsletter" name="newsletter" value="1">
                                <label for="newsletter">Subscribe to our Newsletter</label>
                            </div>

                            <button type="submit" class="submit-btn">
                                <span>Send Message</span>
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-info-side">
                        <h3>Get in Touch</h3>

                        <div class="contact-info-item">
                            <i class="bi bi-envelope-fill"></i>
                            <h4>Email Us</h4>
                            <p>Send us an email anytime</p>
                            <a href="mailto:contact@casprivatecare.online">contact@casprivatecare.online</a>
                        </div>

                        <div class="contact-info-item">
                            <i class="bi bi-telephone-fill"></i>
                            <h4>Call Us</h4>
                            <p>Mon-Fri 9am-6pm EST</p>
                            <a href="tel:+16462828282">+1 (646) 282-8282</a>
                        </div>

                        <div class="contact-info-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <h4>Location</h4>
                            <p>Service Area</p>
                            <p>New York, USA</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contact-spacer"></div>
        </section>
    </main>

    @include('partials.footer')
    @include('partials.mobile-footer')

    <script>
        // Form submission handler
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('.submit-btn');
            const successMsg = document.getElementById('successMessage');
            
            // Add loading state
            btn.classList.add('loading');
            btn.querySelector('span').textContent = 'Sending...';
            
            // Simulate form submission (replace with actual AJAX call)
            setTimeout(() => {
                // Remove loading state
                btn.classList.remove('loading');
                btn.querySelector('span').textContent = 'Send Message';
                
                // Show success message
                successMsg.style.display = 'block';
                
                // Reset form
                this.reset();
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    successMsg.style.display = 'none';
                }, 5000);
                
                // Scroll to success message
                successMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 2000);
        });

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = `(${value}`;
                } else if (value.length <= 6) {
                    value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                } else {
                    value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>