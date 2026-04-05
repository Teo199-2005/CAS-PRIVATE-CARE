# Housekeeper and training center decommission (application layer)

**Status (2026):** Housekeeper and training-center partner types are removed from registration, login, marketing surfaces, admin/Vue tooling, and most dedicated API routes. Database tables and historical columns (e.g. time tracking, booking assignments) are intentionally left in place until a separate data/legal review.

## Stripe / webhooks

- Stripe Connect onboarding paths for decommissioned types were removed from user-facing routes.
- Webhooks may still receive events for legacy Connect accounts; handlers should fail safe (no new onboarding). Filter or no-op legacy payouts in a future change if finance requires it.

## Intentional code references

Some identifiers remain in read-only contexts (reports, legacy booking payloads, migration history). Do not re-wire these to active product flows without an explicit data migration plan.
