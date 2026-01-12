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
            <li class="dropdown" id="servicesDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" onclick="toggleDropdown(event)">
                    Services <i class="bi bi-chevron-down" style="font-size: 0.8rem; margin-left: 0.5rem;"></i>
                </a>
                <div class="dropdown-menu" id="servicesMenu">
                    <a href="{{ url('/caregiver-new-york') }}">Caregiver</a>
                    <a href="{{ url('/housekeeper-new-york') }}">Housekeeper</a>
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
    // Toggle mobile menu
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const isExpanded = navLinks.classList.toggle('active');
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', isExpanded);
        }
    }

    // Toggle dropdown on mobile
    function toggleDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Only toggle on mobile
        if (window.innerWidth <= 768) {
            const dropdown = document.getElementById('servicesDropdown');
            const menu = document.getElementById('servicesMenu');
            
            if (dropdown && menu) {
                const isOpen = dropdown.classList.toggle('open');
                menu.style.display = isOpen ? 'block' : 'none';
            }
        }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const nav = document.querySelector('nav');
        const navLinks = document.getElementById('navLinks');
        const dropdown = document.getElementById('servicesDropdown');
        
        // Close mobile menu if clicking outside
        if (nav && navLinks && !nav.contains(event.target) && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            if (dropdown) {
                dropdown.classList.remove('open');
            }
        }
    });

    // Reset dropdown on window resize
    window.addEventListener('resize', function() {
        const dropdown = document.getElementById('servicesDropdown');
        const menu = document.getElementById('servicesMenu');
        
        if (window.innerWidth > 768) {
            // Desktop mode - reset inline styles
            if (menu) {
                menu.style.display = '';
            }
            if (dropdown) {
                dropdown.classList.remove('open');
            }
        }
    });

    // Ensure proper initialization on page load
    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('servicesMenu');
        if (window.innerWidth > 768 && menu) {
            menu.style.display = '';
        }
    });
</script>
