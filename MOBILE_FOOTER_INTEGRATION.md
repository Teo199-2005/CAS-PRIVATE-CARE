# Script to Add Mobile Footer to All Pages

## Pages that need mobile footer integration:

The mobile footer should be added to all public-facing pages. Here's the list:

### Main Pages:
1. ✅ landing.blade.php - DONE
2. about.blade.php
3. contact.blade.php
4. faq.blade.php
5. blog.blade.php
6. blog/index.blade.php
7. blog/show.blade.php
8. caregiver-new-york.blade.php
9. contractor-partner.blade.php
10. privacy.blade.php
11. terms.blade.php
12. testimonials-faq.blade.php

### How to Add:

Add this line before the closing `</body>` tag and after the main footer:

```blade
<!-- Mobile-Only Footer -->
@include('partials.mobile-footer')
```

### Example:
```blade
    <!-- Desktop Footer -->
    @include('partials.footer')
    
    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')
    
    <!-- Scripts -->
    <script>
    ...
    </script>
</body>
</html>
```

### Notes:
- The mobile footer automatically hides on desktop (>768px)
- The desktop footer automatically hides on mobile (≤768px)
- No conflicts between the two footers
- Both can coexist in the same file

## Auto-Integration Instructions

If you want to automatically add to all pages, search for:
```
@include('partials.footer')
```

And replace with:
```
@include('partials.footer')
    
    <!-- Mobile-Only Footer -->
    @include('partials.mobile-footer')
```

This ensures consistent mobile experience across all pages.
