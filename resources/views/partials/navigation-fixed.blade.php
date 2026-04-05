<nav>
    <div class="nav-container">
        <div class="logo-section">
            <a href="{{ url('/') }}">
                <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo - Professional Caregiving Services" width="150" height="150">
            </a>
        </div>
        <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navLinks" id="mobileMenuBtn">
            <span aria-hidden="true">☰</span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a href="{{ url('/') }}#services">Services</a></li>
            <li><a href="{{ url('/caregiver-new-york') }}">Caregivers</a></li>
            <li><a href="{{ url('/contractors') }}">Careers (W-2)</a></li>
            <li><a href="{{ url('/about') }}">About</a></li>
            <li><a href="{{ url('/blog') }}">Blog</a></li>
            <li><a href="{{ url('/contact') }}">Contact Us</a></li>
            <li><a href="{{ url('/faq') }}">FAQ</a></li>
            <li><a href="{{ url('/login') }}">Login</a></li>
            <li><a href="{{ url('/register') }}" class="cta-btn">Register</a></li>
        </ul>
    </div>
</nav>

<script>
    // Toggle mobile menu
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const isExpanded = navLinks.classList.toggle('active');
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', isExpanded);
        }
    }

    document.addEventListener('click', function(event) {
        const nav = document.querySelector('nav');
        const navLinks = document.getElementById('navLinks');
        if (nav && navLinks && !nav.contains(event.target) && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
        }
    });

    window.addEventListener('resize', function() {
        const navLinks = document.getElementById('navLinks');
        if (window.innerWidth > 768 && navLinks) {
            navLinks.classList.remove('active');
        }
    });
</script>
