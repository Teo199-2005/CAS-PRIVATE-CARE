<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - CAS Private Care LLC</title>
    <meta name="description" content="Privacy Policy for CAS Private Care LLC - How we collect, use, and protect your personal information.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.8;
            color: #1e293b;
            background-color: #f8fafc;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            padding: 2rem 0;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            color: #dc2626;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .header p {
            color: #64748b;
            font-size: 1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3b82f6;
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #2563eb;
        }

        .last-updated {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .content {
            font-size: 1rem;
        }

        .content h2 {
            font-size: 1.75rem;
            color: #1e293b;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .content h3 {
            font-size: 1.35rem;
            color: #334155;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .content h4 {
            font-size: 1.15rem;
            color: #475569;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .content p {
            margin-bottom: 1.25rem;
            color: #475569;
        }

        .content ul, .content ol {
            margin-left: 2rem;
            margin-bottom: 1.25rem;
            color: #475569;
        }

        .content li {
            margin-bottom: 0.75rem;
        }

        .content strong {
            color: #1e293b;
            font-weight: 600;
        }

        .content a {
            color: #3b82f6;
            text-decoration: underline;
        }

        .content a:hover {
            color: #2563eb;
        }

        .highlight-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1.25rem;
            margin: 2rem 0;
            border-radius: 4px;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 1.25rem;
            margin: 2rem 0;
            border-radius: 4px;
        }

        .contact-info {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 1.25rem;
            margin: 2rem 0;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 2px solid #e2e8f0;
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .content h2 {
                font-size: 1.5rem;
            }

            .content h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Back to Home
        </a>

        <div class="header">
            <h1>Privacy Policy</h1>
            <p>CAS Private Care LLC</p>
        </div>

        <div class="last-updated">
            <strong>Last Updated:</strong> {{ date('F j, Y') }}
        </div>

        <div class="content">
            <p>
                At CAS Private Care LLC ("we," "our," or "us"), we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website, mobile application, and services (collectively, the "Service").
            </p>

            <p>
                Please read this Privacy Policy carefully. By using our Service, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, you may not use our Service.
            </p>

            <div class="highlight-box">
                <strong>Your privacy is important to us.</strong> We are committed to being transparent about our data practices and giving you control over your personal information.
            </div>

            <h2>1. Information We Collect</h2>
            
            <h3>1.1 Information You Provide to Us</h3>
            <p>We collect information that you voluntarily provide to us when you:</p>
            <ul>
                <li><strong>Create an Account:</strong> Name, email address, phone number, password, ZIP code, date of birth, address, and other profile information</li>
                <li><strong>Complete Your Profile:</strong> Additional information such as emergency contacts, medical conditions (for clients), qualifications and certifications (for service providers), training certificates, bio, years of experience, and other professional details</li>
                <li><strong>Book Services:</strong> Service preferences, scheduling information, special requirements, and payment information</li>
                <li><strong>Communicate with Us:</strong> Information you provide when contacting our support team, submitting inquiries, or providing feedback</li>
                <li><strong>Upload Documents:</strong> Training certificates, identification documents, background check documents, and other files you upload to your profile</li>
                <li><strong>Post Reviews:</strong> Reviews, ratings, and comments about services received or provided</li>
            </ul>

            <h3>1.2 Information We Collect Automatically</h3>
            <p>When you use our Service, we automatically collect certain information, including:</p>
            <ul>
                <li><strong>Usage Data:</strong> Information about how you access and use our Service, including pages visited, features used, time spent on pages, and navigation patterns</li>
                <li><strong>Device Information:</strong> IP address, browser type and version, device type, operating system, and device identifiers</li>
                <li><strong>Location Data:</strong> General location information based on your IP address or ZIP code (we do not track precise GPS location without your explicit consent)</li>
                <li><strong>Log Data:</strong> Server logs, including access times, pages viewed, and error logs</li>
                <li><strong>Cookies and Tracking Technologies:</strong> Information collected through cookies, web beacons, and similar technologies (see "Cookies and Tracking Technologies" section below)</li>
            </ul>

            <h3>1.3 Information from Third Parties</h3>
            <p>We may receive information about you from third parties, including:</p>
            <ul>
                <li><strong>Social Media Platforms:</strong> If you choose to register or log in using Google, Facebook, or other social media accounts, we may receive information from these platforms</li>
                <li><strong>Payment Processors:</strong> Information from payment processors regarding transactions and payment methods</li>
                <li><strong>Background Check Services:</strong> If applicable, information from background check providers</li>
                <li><strong>Training Centers:</strong> Information about certifications and training completed</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>We use the information we collect for various purposes, including:</p>

            <h3>2.1 Providing and Improving Our Service</h3>
            <ul>
                <li>Creating and managing your account</li>
                <li>Facilitating connections between clients and service providers</li>
                <li>Processing bookings and payments</li>
                <li>Managing service assignments and scheduling</li>
                <li>Providing customer support and responding to inquiries</li>
                <li>Improving and optimizing our Service</li>
                <li>Developing new features and functionality</li>
            </ul>

            <h3>2.2 Communication</h3>
            <ul>
                <li>Sending service-related notifications, updates, and reminders</li>
                <li>Responding to your questions and requests</li>
                <li>Sending administrative information, including changes to our terms, conditions, and policies</li>
                <li>Providing marketing communications (with your consent, where required by law)</li>
                <li>Facilitating communication between users on our platform</li>
            </ul>

            <h3>2.3 Safety and Security</h3>
            <ul>
                <li>Verifying user identities and preventing fraud</li>
                <li>Conducting background checks and verification (for service providers)</li>
                <li>Detecting, preventing, and addressing technical issues, fraud, or illegal activity</li>
                <li>Ensuring the safety and security of our users</li>
                <li>Enforcing our Terms of Service and other policies</li>
            </ul>

            <h3>2.4 Legal and Compliance</h3>
            <ul>
                <li>Complying with applicable laws, regulations, and legal processes</li>
                <li>Responding to requests from government authorities</li>
                <li>Protecting our rights, property, and safety, as well as that of our users and others</li>
                <li>Resolving disputes and enforcing our agreements</li>
            </ul>

            <h3>2.5 Business Operations</h3>
            <ul>
                <li>Analyzing usage patterns and trends</li>
                <li>Conducting research and analytics</li>
                <li>Generating reports and insights</li>
                <li>Managing our business operations</li>
            </ul>

            <h2>3. How We Share Your Information</h2>
            <p>We may share your information in the following circumstances:</p>

            <h3>3.1 With Other Users</h3>
            <p>To facilitate service connections, we share certain information:</p>
            <ul>
                <li><strong>For Clients:</strong> Your name, service requirements, location (general area), and booking information with service providers</li>
                <li><strong>For Service Providers:</strong> Your name, profile information, qualifications, certifications, ratings, reviews, and availability with clients</li>
            </ul>

            <h3>3.2 With Service Providers</h3>
            <p>We may share information with third-party service providers who perform services on our behalf, including:</p>
            <ul>
                <li>Payment processing and financial services</li>
                <li>Cloud storage and hosting services</li>
                <li>Email and communication services</li>
                <li>Analytics and data analysis services</li>
                <li>Background check and verification services</li>
                <li>Customer support services</li>
            </ul>
            <p>These service providers are contractually obligated to protect your information and use it only for the purposes we specify.</p>

            <h3>3.3 For Legal Reasons</h3>
            <p>We may disclose your information if required or permitted by law, including:</p>
            <ul>
                <li>To comply with legal obligations, court orders, or government requests</li>
                <li>To enforce our Terms of Service and other agreements</li>
                <li>To protect our rights, property, or safety, or that of our users</li>
                <li>To investigate potential violations or fraud</li>
                <li>In connection with a merger, acquisition, or sale of assets</li>
            </ul>

            <h3>3.4 With Your Consent</h3>
            <p>We may share your information with third parties when you explicitly consent to such sharing.</p>

            <h2>4. Cookies and Tracking Technologies</h2>
            <p>
                We use cookies and similar tracking technologies to collect and store information about your preferences and activity on our Service. Cookies are small data files stored on your device.
            </p>

            <h3>4.1 Types of Cookies We Use</h3>
            <ul>
                <li><strong>Essential Cookies:</strong> Required for the Service to function properly (e.g., authentication, security)</li>
                <li><strong>Functional Cookies:</strong> Remember your preferences and settings to enhance your experience</li>
                <li><strong>Analytics Cookies:</strong> Help us understand how users interact with our Service</li>
                <li><strong>Advertising Cookies:</strong> Used to deliver relevant advertisements (with your consent, where required)</li>
            </ul>

            <h3>4.2 Managing Cookies</h3>
            <p>
                Most web browsers allow you to control cookies through their settings. You can set your browser to refuse cookies or to alert you when cookies are being sent. However, disabling cookies may limit your ability to use certain features of our Service.
            </p>

            <h2>5. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
            </p>
            <ul>
                <li>Encryption of data in transit and at rest</li>
                <li>Secure servers and databases</li>
                <li>Access controls and authentication procedures</li>
                <li>Regular security assessments and updates</li>
                <li>Employee training on data protection</li>
            </ul>
            <div class="info-box">
                <p>
                    <strong>Important:</strong> While we strive to protect your information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security of your data.
                </p>
            </div>

            <h2>6. Data Retention</h2>
            <p>
                We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. Factors we consider when determining retention periods include:
            </p>
            <ul>
                <li>The nature of the information</li>
                <li>The purposes for which it was collected</li>
                <li>Legal and regulatory requirements</li>
                <li>The potential risk of harm from unauthorized use or disclosure</li>
            </ul>
            <p>
                When we no longer need your information, we will securely delete or anonymize it in accordance with our data retention policies.
            </p>

            <h2>7. Your Privacy Rights</h2>
            <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>

            <h3>7.1 Access and Portability</h3>
            <p>You have the right to access and receive a copy of your personal information we hold about you.</p>

            <h3>7.2 Correction</h3>
            <p>You have the right to request correction of inaccurate or incomplete personal information.</p>

            <h3>7.3 Deletion</h3>
            <p>You have the right to request deletion of your personal information, subject to certain exceptions (e.g., legal obligations, legitimate business interests).</p>

            <h3>7.4 Objection and Restriction</h3>
            <p>You have the right to object to or request restriction of processing of your personal information in certain circumstances.</p>

            <h3>7.5 Withdrawal of Consent</h3>
            <p>Where processing is based on consent, you have the right to withdraw your consent at any time.</p>

            <h3>7.6 Opt-Out of Marketing</h3>
            <p>You can opt-out of receiving marketing communications from us by using the unsubscribe link in our emails or by contacting us directly.</p>

            <p>
                To exercise any of these rights, please contact us using the information provided in the "Contact Us" section below. We will respond to your request in accordance with applicable law.
            </p>

            <h2>8. Children's Privacy</h2>
            <p>
                Our Service is not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If we become aware that we have collected information from a child under 18, we will take steps to delete such information promptly. If you believe we have collected information from a child, please contact us immediately.
            </p>

            <h2>9. Third-Party Links</h2>
            <p>
                Our Service may contain links to third-party websites or services. We are not responsible for the privacy practices or content of these third-party sites. We encourage you to review the privacy policies of any third-party sites you visit.
            </p>

            <h2>10. International Data Transfers</h2>
            <p>
                Your information may be transferred to and processed in countries other than your country of residence. These countries may have data protection laws that differ from those in your country. By using our Service, you consent to the transfer of your information to these countries. We take appropriate safeguards to ensure your information is protected in accordance with this Privacy Policy.
            </p>

            <h2>11. California Privacy Rights</h2>
            <p>
                If you are a California resident, you have additional rights under the California Consumer Privacy Act (CCPA), including:
            </p>
            <ul>
                <li>The right to know what personal information we collect, use, disclose, and sell</li>
                <li>The right to delete your personal information</li>
                <li>The right to opt-out of the sale of your personal information (we do not sell your personal information)</li>
                <li>The right to non-discrimination for exercising your privacy rights</li>
            </ul>
            <p>
                To exercise these rights, please contact us using the information provided below.
            </p>

            <h2>12. Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy from time to time to reflect changes in our practices, technology, legal requirements, or other factors. We will notify you of any material changes by:
            </p>
            <ul>
                <li>Posting the updated Privacy Policy on this page</li>
                <li>Updating the "Last Updated" date</li>
                <li>Sending you an email notification (for significant changes)</li>
                <li>Providing a prominent notice on our Service (for material changes)</li>
            </ul>
            <p>
                Your continued use of our Service after any changes to this Privacy Policy constitutes acceptance of the updated policy.
            </p>

            <h2>13. Contact Us</h2>
            <div class="contact-info">
                <p>
                    If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:
                </p>
                <p>
                    <strong>CAS Private Care LLC</strong><br>
                    Email: <a href="mailto:contact@casprivatecare.online">contact@casprivatecare.online</a><br>
                    Phone: <a href="tel:+16462828282">+1 (646) 282-8282</a><br>
                    Address: New York, USA
                </p>
                <p style="margin-top: 1rem;">
                    <strong>Data Protection Officer:</strong><br>
                    If you have concerns about how we handle your personal information, you may also contact our Data Protection Officer at the email address above.
                </p>
            </div>

            <div class="highlight-box">
                <p>
                    <strong>Thank you for trusting CAS Private Care LLC with your personal information. We are committed to protecting your privacy and being transparent about our data practices.</strong>
                </p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} CAS Private Care LLC. All rights reserved.</p>
            <p style="margin-top: 1rem;">
                <a href="{{ url('/privacy') }}">Privacy Policy</a> | 
                <a href="{{ url('/terms') }}">Terms of Service</a> | 
                <a href="{{ url('/') }}">Home</a>
            </p>
        </div>
    </div>
</body>
</html>






