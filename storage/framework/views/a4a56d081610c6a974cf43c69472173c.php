 <nav>
    <div class="nav-container">
        <div class="logo-section">
            <a href="<?php echo e(url('/')); ?>">
                <img src="<?php echo e(asset('logo flower.png')); ?>" alt="CAS Private Care LLC Logo - Professional Caregiving Services" width="150" height="150">
            </a>
        </div>
        <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navLinks" id="mobileMenuBtn">
            <span aria-hidden="true">☰</span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="<?php echo e(url('/')); ?>">Home</a></li>
            <li class="dropdown" id="servicesDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle" onclick="toggleDropdown(event)">
                    Services <i class="bi bi-chevron-down" style="font-size: 0.8rem; margin-left: 0.5rem;"></i>
                </a>
                <div class="dropdown-menu" id="servicesMenu">
                    <a href="<?php echo e(url('/caregiver-new-york')); ?>">Caregiver</a>
                    <a href="<?php echo e(url('/')); ?>#services">Housekeeping</a>
                    <a href="<?php echo e(url('/')); ?>#services">Personal Assistant</a>
                </div>
            </li>
            <li><a href="<?php echo e(url('/contractor-partner')); ?>">1099 Contractors</a></li>
            <li><a href="<?php echo e(url('/')); ?>#training">Training</a></li>
            <li><a href="<?php echo e(url('/about')); ?>">About</a></li>
            <li><a href="<?php echo e(url('/blog')); ?>">Blog</a></li>
            <li><a href="<?php echo e(url('/contact')); ?>">Contact Us</a></li>
            <li><a href="<?php echo e(url('/faq')); ?>">FAQ</a></li>
            <li><a href="<?php echo e(url('/login')); ?>">Login</a></li>
            <li><a href="<?php echo e(url('/register')); ?>" class="cta-btn">Register</a></li>
        </ul>
    </div>
</nav>

<script>
    // Toggle mobile menu
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');
        const menuBtn = document.getElementById('mobileMenuBtn');
        const dropdown = document.getElementById('servicesDropdown');
        const servicesMenu = document.getElementById('servicesMenu');
        
        const isExpanded = navLinks.classList.toggle('active');
        
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', isExpanded);
        }
        
        // Reset dropdown when closing menu
        if (!isExpanded && dropdown) {
            dropdown.classList.remove('open');
            if (servicesMenu) {
                servicesMenu.style.display = '';
            }
        }
    }

    // Toggle dropdown on mobile
    function toggleDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const dropdown = document.getElementById('servicesDropdown');
        const menu = document.getElementById('servicesMenu');
        
        // Only toggle on mobile
        if (window.innerWidth <= 768) {
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
        const servicesMenu = document.getElementById('servicesMenu');
        
        // Close mobile menu if clicking outside
        if (nav && navLinks && !nav.contains(event.target) && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            if (dropdown) {
                dropdown.classList.remove('open');
            }
            if (servicesMenu && window.innerWidth <= 768) {
                servicesMenu.style.display = '';
            }
        }
    });

    // Reset dropdown on window resize
    window.addEventListener('resize', function() {
        const dropdown = document.getElementById('servicesDropdown');
        const menu = document.getElementById('servicesMenu');
        const navLinks = document.getElementById('navLinks');
        
        if (window.innerWidth > 768) {
            // Desktop mode - reset inline styles
            if (menu) {
                menu.style.display = '';
            }
            if (dropdown) {
                dropdown.classList.remove('open');
            }
            if (navLinks) {
                navLinks.classList.remove('active');
            }
        }
    });

    // Ensure proper initialization on page load
    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('servicesMenu');
        const navLinks = document.getElementById('navLinks');
        
        // Reset everything on load
        if (window.innerWidth > 768) {
            if (menu) {
                menu.style.display = '';
            }
            if (navLinks) {
                navLinks.classList.remove('active');
            }
        }
    });
</script>
<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/partials/navigation.blade.php ENDPATH**/ ?>