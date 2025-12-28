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
            <li class="dropdown">
                <a href="{{ url('/') }}#services">Services <i class="bi bi-chevron-down" style="font-size: 0.8rem; margin-left: 0.5rem;"></i></a>
                <div class="dropdown-menu">
                    <a href="{{ url('/caregiver-new-york') }}">Caregiver</a>
                    <a href="{{ url('/') }}#services">Housekeeping</a>
                    <a href="{{ url('/') }}#services">Personal Assistant</a>
                </div>
            </li>
            <li><a href="{{ url('/contractor-partner') }}">1099 Contractors</a></li>
            <li><a href="{{ url('/') }}#training">Training</a></li>
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
</script>

