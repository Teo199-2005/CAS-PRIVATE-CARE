# Developer Onboarding Guide - CAS Private Care

**Last Updated:** January 25, 2026  
**Version:** 1.0

---

## Welcome! 👋

This guide will get you from zero to productive in under 4 hours.

---

## 1. Prerequisites

### Required Software
- **PHP 8.2+** with extensions: `mbstring, xml, curl, mysql, gd, zip`
- **Composer 2.x** (PHP package manager)
- **Node.js 20+** with npm
- **MySQL 8.0** or SQLite (for local development)
- **Git** (version control)

### Recommended Tools
- **VS Code** with extensions:
  - Volar (Vue 3)
  - PHP Intelephense
  - Laravel Blade Snippets
  - ESLint
- **TablePlus** or **DBeaver** (database GUI)
- **Postman** or **Insomnia** (API testing)

---

## 2. Quick Start (15 minutes)

```bash
# 1. Clone repository
git clone https://github.com/Teo199-2005/CAS-PRIVATE-CARE.git
cd CAS-PRIVATE-CARE

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Create database (SQLite for quick start)
touch database/database.sqlite

# 7. Update .env for SQLite
# Change these lines:
#   DB_CONNECTION=sqlite
#   DB_DATABASE=database/database.sqlite

# 8. Run migrations
php artisan migrate

# 9. Seed sample data (optional but recommended)
php artisan db:seed

# 10. Build frontend assets
npm run build

# 11. Start development server
php artisan serve
```

**Visit:** http://localhost:8000

---

## 3. Project Structure

```
├── app/                    # PHP application code
│   ├── Http/
│   │   ├── Controllers/   # Request handlers
│   │   └── Middleware/    # Request filters
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic
├── config/                 # Configuration files
├── database/
│   ├── migrations/        # Database schema
│   └── seeders/           # Sample data
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/
│   │   ├── components/    # Vue 3 components
│   │   └── composables/   # Vue composition API
│   └── views/             # Blade templates
├── routes/
│   ├── api.php            # API routes
│   └── web.php            # Web routes
├── tests/
│   ├── Feature/           # Integration tests
│   ├── Unit/              # Unit tests
│   └── Browser/           # Dusk browser tests
└── docs/                   # Documentation
```

---

## 4. Key Technologies

| Layer | Technology | Documentation |
|-------|------------|---------------|
| Backend | Laravel 11 | laravel.com/docs |
| Frontend | Vue 3 + Vuetify 3 | vuejs.org, vuetifyjs.com |
| Build | Vite 7 | vitejs.dev |
| Database | MySQL/SQLite | - |
| Payments | Stripe | stripe.com/docs |
| Email | Brevo (Sendinblue) | developers.brevo.com |

---

## 5. Development Workflow

### Running Local Server

```bash
# Terminal 1: PHP server
php artisan serve

# Terminal 2: Vite dev server (hot reload)
npm run dev
```

**Hot Reload:** Changes to `.vue` files update instantly.

### Running Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/Auth/LoginTest.php

# With coverage
php artisan test --coverage

# Parallel (faster)
php artisan test --parallel
```

### Code Quality

```bash
# PHP formatting (auto-fix)
./vendor/bin/pint

# PHP formatting (check only)
./vendor/bin/pint --test

# ESLint (when configured)
npm run lint
```

---

## 6. User Roles

| Role | Dashboard URL | Description |
|------|---------------|-------------|
| Client | `/client-dashboard` | Book services, pay, view history |
| Caregiver | `/caregiver-dashboard` | View assignments, track time |
| Housekeeper | `/housekeeper-dashboard` | View cleaning jobs |
| Admin | `/admin-dashboard` | Full system access |
| Admin Staff | `/admin-dashboard` | Limited admin access |
| Marketing | `/marketing-dashboard` | Referrals, commissions |
| Training | `/training-dashboard` | Training management |

### Test Accounts (after seeding)

```
Admin:      admin@cascare.com / password
Client:     client@test.com / password
Caregiver:  caregiver@test.com / password
```

---

## 7. Common Tasks

### Create a New API Endpoint

```php
// 1. Add route in routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-endpoint', [MyController::class, 'index']);
});

// 2. Create controller
php artisan make:controller Api/MyController

// 3. Implement method
public function index() {
    return response()->json(['data' => 'value']);
}
```

### Create a New Vue Component

```vue
<!-- resources/js/components/MyComponent.vue -->
<template>
  <v-card>
    <v-card-title>{{ title }}</v-card-title>
    <v-card-text>
      <slot></slot>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  title: { type: String, required: true }
});
</script>
```

### Add a Database Column

```bash
# Create migration
php artisan make:migration add_column_to_table --table=users

# Edit migration file
# Run migration
php artisan migrate
```

---

## 8. Debugging

### Laravel Telescope (Development)

```bash
# Install
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Access at: http://localhost:8000/telescope
```

Features:
- View all SQL queries
- See mail sent
- Debug jobs/queues
- Exception tracking

### Frontend Debugging

1. **Vue DevTools** - Install browser extension
2. **Network Tab** - Watch API calls
3. **Console** - Check for errors

### Common Issues

| Problem | Solution |
|---------|----------|
| 500 Error | Check `storage/logs/laravel.log` |
| Vue not updating | Clear cache: `php artisan cache:clear` |
| CSS not loading | Rebuild: `npm run build` |
| Auth issues | Check `config/auth.php` guards |

---

## 9. Git Workflow

```bash
# 1. Create feature branch
git checkout -b feature/my-feature

# 2. Make changes and commit
git add .
git commit -m "feat: add new feature"

# 3. Push and create PR
git push origin feature/my-feature
```

### Commit Message Format

```
type: short description

Types:
- feat: New feature
- fix: Bug fix
- docs: Documentation
- style: Formatting
- refactor: Code restructuring
- test: Adding tests
- chore: Maintenance
```

---

## 10. Deployment

### Production Checklist

```bash
# 1. Update .env for production
APP_ENV=production
APP_DEBUG=false

# 2. Install dependencies (no dev)
composer install --no-dev --optimize-autoloader

# 3. Build assets
npm run build

# 4. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run migrations
php artisan migrate --force
```

---

## 11. Getting Help

1. **Docs folder** - Check `/docs` for detailed guides
2. **Audit files** - `COMPREHENSIVE_*` files have system overviews
3. **Code comments** - Most files have inline documentation
4. **Team** - Ask questions, we're here to help!

### Key Documentation Files

| File | Description |
|------|-------------|
| `docs/PHASE_3_IMPROVEMENTS.md` | Latest architecture decisions |
| `PRODUCTION_DEPLOYMENT_CHECKLIST.md` | Deployment guide |
| `SENTRY_MONITORING_SETUP.md` | Error tracking setup |
| `docs/_routes.txt` | All available routes |

---

## 12. Next Steps

1. ✅ Run the application locally
2. ✅ Login with test accounts
3. ✅ Explore each dashboard
4. ✅ Run the test suite
5. ✅ Make a small change and see hot reload
6. ✅ Read a component file (`ClientDashboard.vue`)
7. ✅ Check Telescope for query insights

**Estimated time to first contribution:** 4 hours

---

Welcome to the team! 🎉
