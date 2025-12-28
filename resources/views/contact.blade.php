<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    
    <!-- Primary Meta Tags -->
    <title>Contact Us | CAS Private Care LLC | Get in Touch</title>
    <meta name="title" content="Contact Us | CAS Private Care LLC | Get in Touch">
    <meta name="description" content="Contact CAS Private Care LLC for questions about our caregiving services, caregiver partnerships, or general inquiries. We're here to help connect families with quality care.">
    <meta name="keywords" content="contact cas private care, caregiver service contact, home care contact, caregiving support">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/contact') }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/contact') }}">
    <meta property="og:title" content="Contact Us | CAS Private Care LLC">
    <meta property="og:description" content="Contact CAS Private Care LLC for questions about our caregiving services or caregiver partnerships.">
    <meta property="og:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/contact') }}">
    <meta property="twitter:title" content="Contact Us | CAS Private Care LLC">
    <meta property="twitter:description" content="Contact CAS Private Care LLC for questions about our caregiving services or caregiver partnerships.">
    <meta property="twitter:image" content="{{ asset('logo flower.png') }}">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @include('partials.nav-footer-styles')
    
    <style>
        body {
            font-family: 'Sora', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #1e293b;
            background-color: #ffffff;
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
    @include('partials.navigation')

    <main style="padding-top: 88px;">
        <!-- Contact Section -->
        <section class="section-light">
            <div class="container">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: #1e40af; margin-bottom: 3rem; text-align: center;">Contact Us</h2>
                
                @if(session('success'))
                    <div class="alert alert-success" style="background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto;">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error" style="background: #ef4444; color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; max-width: 800px; margin-left: auto; margin-right: auto;">
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 4rem; align-items: start;">
                    <!-- Contact Form -->
                    <div>
                        <h3 style="font-size: 1.75rem; font-weight: 700; color: #1e40af; margin-bottom: 2rem;">Send Us a Message</h3>
                        
                        <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                            @csrf
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="first_name">First Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                                    @error('first_name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                                    @error('last_name')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span style="color: #ef4444;">*</span></label>
                                    <input type="tel" id="phone" name="phone" placeholder="(646) 282-8282" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span style="color: #ef4444;">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="service_type">Type of Service <span style="color: #ef4444;">*</span></label>
                                    <select id="service_type" name="service_type" required>
                                        <option value="">Select a service type</option>
                                        <option value="Home Care" {{ old('service_type') == 'Home Care' ? 'selected' : '' }}>Home Care</option>
                                        <option value="Private Duty Nursing" {{ old('service_type') == 'Private Duty Nursing' ? 'selected' : '' }}>Private Duty Nursing</option>
                                        <option value="Care Management" {{ old('service_type') == 'Care Management' ? 'selected' : '' }}>Care Management</option>
                                        <option value="Bedside Care" {{ old('service_type') == 'Bedside Care' ? 'selected' : '' }}>Bedside Care</option>
                                    </select>
                                    @error('service_type')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="location">Location where services would be provided <span style="color: #ef4444;">*</span></label>
                                    <select id="location" name="location" required>
                                        <option value="">Select a location</option>
                                        <option value="New York City (Inclusive of five boroughs)" {{ old('location') == 'New York City (Inclusive of five boroughs)' ? 'selected' : '' }}>New York City (Inclusive of five boroughs)</option>
                                        <option value="Long Island" {{ old('location') == 'Long Island' ? 'selected' : '' }}>Long Island</option>
                                        <option value="Westchester" {{ old('location') == 'Westchester' ? 'selected' : '' }}>Westchester</option>
                                        <option value="New Jersey" {{ old('location') == 'New Jersey' ? 'selected' : '' }}>New Jersey</option>
                                        <option value="Connecticut" {{ old('location') == 'Connecticut' ? 'selected' : '' }}>Connecticut</option>
                                    </select>
                                    @error('location')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="additional_info">Additional Information</label>
                                <textarea id="additional_info" name="additional_info" rows="5" placeholder="Tell us more about your needs...">{{ old('additional_info') }}</textarea>
                                @error('additional_info')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label style="display: flex; align-items: center; cursor: pointer; font-weight: 400;">
                                    <input type="checkbox" name="newsletter" value="1" {{ old('newsletter') ? 'checked' : '' }} style="width: auto; margin-right: 0.75rem; cursor: pointer;">
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

    @include('partials.footer')

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

