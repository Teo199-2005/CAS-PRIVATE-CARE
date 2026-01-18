# Payroll & Tax System Implementation

## Overview

This document describes the comprehensive payroll and tax management system implementation for contractors (caregivers, housekeepers, marketing partners, and training centers).

## Features Implemented

### 1. Contractor Onboarding with Tax Forms
- W9 form digital submission
- Tax classification selection (Individual, LLC, Corporation, etc.)
- TIN/SSN/EIN collection
- Admin verification workflow
- Document storage and tracking

### 2. Scheduled Payments
- Configurable payout frequencies: Weekly, Bi-weekly, Monthly
- Automatic payout processing via scheduled commands
- Minimum payout thresholds
- Preferred payout day selection

### 3. Automatic Tax Calculations
- Year-to-date earnings tracking
- Self-employment tax estimation (15.3%)
- Federal income tax estimation
- Quarterly payment reminders
- Real-time tax liability display

### 4. Compliance Checks
- Automated compliance verification
- W9 submission status
- Bank account connection status
- Certification verification (for caregivers)
- Background check status tracking

### 5. 1099-NEC Form Generation
- Annual 1099 form generation for contractors earning $600+
- Draft, review, finalize, send workflow
- PDF download capability
- Correction handling
- Bulk processing

---

## Database Schema

### New Tables Created

#### `tax_documents`
Stores all tax-related documents submitted by contractors.
```sql
- id, user_id, document_type (w9, w4, etc.)
- legal_name, business_name, tax_classification
- tin (encrypted), tin_type (SSN/EIN/ITIN)
- address, city, state, zip
- certification_date, verified_at, verified_by
- status, file_path
```

#### `tax_forms_1099`
Stores generated 1099-NEC forms.
```sql
- id, user_id, tax_year, form_type
- total_compensation, federal_income_tax_withheld
- state_income, state_tax_withheld
- recipient_name, recipient_address
- tin, tin_type, status
- generated_at, sent_at, is_corrected
```

#### `scheduled_payouts`
Tracks scheduled payout runs.
```sql
- id, frequency, scheduled_date
- started_at, completed_at
- total_contractors, successful_payouts, failed_payouts
- total_amount, status, notes
```

#### `payout_settings`
System-wide payout configuration.
```sql
- id, key, value, type, description
```

#### `compliance_checks`
Compliance verification records.
```sql
- id, user_id, check_date, check_type
- check_results (JSON), overall_compliant
- checked_by, notes
```

### User Table Additions
New columns added to the `users` table:
```sql
- w9_submitted, w9_verified, w9_submitted_at, w9_verified_at
- onboarding_step, onboarding_completed, onboarding_completed_at
- payout_frequency, payout_minimum_amount, payout_day
- tax_classification, business_name, ein
```

---

## API Endpoints

### Contractor Endpoints (Authenticated)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/tax/w9` | Submit W9 form |
| GET | `/api/tax/w9-status` | Get W9 submission status |
| GET | `/api/tax/onboarding-status` | Get onboarding completion status |
| POST | `/api/tax/onboarding-step` | Update onboarding step |
| GET | `/api/payroll/schedule` | Get payout schedule |
| POST | `/api/payroll/preferences` | Update payout preferences |
| GET | `/api/payroll/history` | Get payout history |
| GET | `/api/payroll/onboarding` | Get onboarding status |
| GET | `/api/tax/estimate` | Get tax estimate |
| GET | `/api/tax/compliance` | Get compliance status |
| GET | `/api/tax/1099` | Get available 1099 forms |
| GET | `/api/tax/1099/{formId}/download` | Download 1099 PDF |

### Admin Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/tax-documents` | Get all tax documents |
| POST | `/api/admin/tax-documents/{id}/verify` | Verify tax document |
| POST | `/api/admin/tax-documents/{id}/reject` | Reject tax document |
| GET | `/api/admin/tax-documents/{id}/download` | Download document |
| GET | `/api/admin/contractor-tax-summary` | Get contractor tax summary |
| GET | `/api/admin/payroll/settings` | Get payout settings |
| POST | `/api/admin/payroll/settings` | Update payout settings |
| GET | `/api/admin/payroll/scheduled-payouts` | Get scheduled payouts |
| POST | `/api/admin/payroll/trigger` | Manually trigger payout |
| GET | `/api/admin/payroll/{payoutId}` | Get payout details |
| GET | `/api/admin/payroll/pending-contractors` | Get contractors pending payout |
| POST | `/api/admin/compliance/run-checks` | Run compliance checks |
| GET | `/api/admin/compliance/summary` | Get compliance summary |
| GET | `/api/admin/1099/summary` | Get 1099 summary |
| GET | `/api/admin/1099/forms` | Get all 1099 forms |
| POST | `/api/admin/1099/generate` | Generate 1099 forms |
| GET | `/api/admin/1099/preview/{userId}` | Preview 1099 for user |
| POST | `/api/admin/1099/{formId}/finalize` | Finalize 1099 form |
| POST | `/api/admin/1099/{formId}/send` | Send 1099 to contractor |
| POST | `/api/admin/1099/bulk-finalize` | Bulk finalize forms |
| POST | `/api/admin/1099/bulk-send` | Bulk send forms |
| GET | `/api/admin/1099/{formId}/download` | Download 1099 PDF |
| POST | `/api/admin/1099/{formId}/correction` | Create 1099 correction |
| GET | `/api/admin/1099/contractors-requiring` | Get contractors requiring 1099 |

---

## Console Commands

### `payouts:process`
Processes scheduled contractor payouts.

```bash
# Run for all frequencies due today
php artisan payouts:process

# Run for specific frequency
php artisan payouts:process weekly

# Preview without processing
php artisan payouts:process --dry-run

# Force run regardless of schedule
php artisan payouts:process --force
```

**Schedule:** Daily at 6:00 AM

### `tax:generate-1099`
Generates 1099-NEC forms for contractors.

```bash
# Generate for previous year (default)
php artisan tax:generate-1099

# Generate for specific year
php artisan tax:generate-1099 2024

# Preview only
php artisan tax:generate-1099 --preview

# Generate and send email notifications
php artisan tax:generate-1099 --email
```

**Schedule:** January 15th at 3:00 AM

### `compliance:check`
Runs compliance checks for contractors.

```bash
# Check all contractors
php artisan compliance:check

# Check specific user
php artisan compliance:check --user=123

# Check specific user type
php artisan compliance:check --type=caregiver

# Show only non-compliant
php artisan compliance:check --failed-only
```

**Schedule:** Weekly on Mondays at 2:00 AM

---

## Services

### `TaxEstimationService`
Calculates tax estimates for contractors.
- Year-to-date earnings calculation
- Self-employment tax (15.3%)
- Federal income tax estimation
- Quarterly payment schedule

### `ScheduledPayoutService`
Manages scheduled payouts.
- Processes payouts by frequency
- Calculates pending earnings
- Creates payout transactions
- Tracks payout history

### `Form1099Service`
Handles 1099 form generation.
- Identifies contractors requiring 1099 ($600+ threshold)
- Generates form data
- Tracks form status

### `ComplianceService`
Runs compliance checks.
- W9 verification
- Bank connection status
- Certification validation
- Background check status

---

## Vue Components

### `TaxPayrollSection.vue`
Reusable component for contractor dashboards showing:
- Onboarding progress
- W9 submission form
- YTD earnings and tax estimates
- 1099 form downloads
- Payout preferences

---

## Files Created/Modified

### New Files Created

#### Migrations
- `database/migrations/2026_01_16_000001_add_contractor_onboarding_fields.php`
- `database/migrations/2026_01_16_000002_create_tax_and_payout_tables.php`

#### Models
- `app/Models/TaxDocument.php`
- `app/Models/TaxForm1099.php`
- `app/Models/ScheduledPayout.php`
- `app/Models/PayoutSetting.php`
- `app/Models/ComplianceCheck.php`

#### Services
- `app/Services/ComplianceService.php`
- `app/Services/TaxEstimationService.php`
- `app/Services/Form1099Service.php`
- `app/Services/ScheduledPayoutService.php`

#### Controllers
- `app/Http/Controllers/TaxDocumentController.php`
- `app/Http/Controllers/PayrollController.php`
- `app/Http/Controllers/Form1099Controller.php`

#### Console Commands
- `app/Console/Commands/ProcessScheduledPayouts.php`
- `app/Console/Commands/Generate1099Forms.php`
- `app/Console/Commands/RunComplianceChecks.php`

#### Vue Components
- `resources/js/components/TaxPayrollSection.vue`

### Modified Files
- `app/Console/Kernel.php` - Added scheduled commands
- `app/Models/User.php` - Added new fields and relationships
- `routes/web.php` - Added new API routes
- `resources/js/app.js` - Registered TaxPayrollSection component

---

## Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Payout Settings
The migration automatically seeds default payout settings.

### 3. Build Frontend
```bash
npm run build
# or for development
npm run dev
```

### 4. Test Commands
```bash
# Preview scheduled payouts
php artisan payouts:process --dry-run

# Preview 1099 generation
php artisan tax:generate-1099 --preview

# Run compliance checks
php artisan compliance:check
```

### 5. Configure Scheduler
Ensure the Laravel scheduler is running:
```bash
# On production
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# On Windows (Task Scheduler)
php artisan schedule:run
```

---

## Payout Schedule

| Frequency | Processing Day | Covering Period |
|-----------|----------------|-----------------|
| Weekly | Friday | Previous week (Mon-Sun) |
| Bi-weekly | Every other Friday | Previous 2 weeks |
| Monthly | 1st of month | Previous month |

---

## Tax Calculation Example

For a contractor earning $5,000 YTD:

| Tax Type | Rate | Amount |
|----------|------|--------|
| Self-Employment Tax | 15.3% | $765.00 |
| Federal Income Tax (est.) | 12% | $600.00 |
| **Total Estimated** | | **$1,365.00** |

---

## Compliance Checklist

For a contractor to be compliant:
1. ✅ W9 form submitted and verified
2. ✅ Bank account connected via Stripe Connect
3. ✅ Profile information complete
4. ✅ Certifications uploaded (caregivers only)
5. ✅ Background check completed (if required)

---

## Security Notes

1. **TIN/SSN Storage**: TINs are stored encrypted in the database
2. **Admin Verification**: All W9 submissions require admin verification
3. **Audit Trail**: All actions are logged with user IDs and timestamps
4. **Access Control**: Admin-only endpoints are protected by middleware

---

## Future Enhancements

1. PDF generation for 1099 forms (requires TCPDF or DomPDF)
2. Email notifications for payout confirmations
3. State-specific tax calculations
4. Integration with IRS e-file system
5. Automatic backup withholding for missing TINs
