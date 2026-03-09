# Stripe Payment Configuration — Mock Setup

**Date:** 2026-02-07
**Status:** Mock keys seeded, code bug fixed. Needs real API keys for production.

## What Was Done

### 1. Bug Fix — StripePaymentController::stripe()

- **File:** `app/Http/Controllers/Bills/StripePaymentController.php`
- **Issue:** `Crypt::decrypt($code)` was called twice — once inside a `try/catch` (correct) and again outside it (crashed on invalid codes)
- **Fix:** Removed the duplicate `Crypt::decrypt()` call

### 2. Mock Stripe Keys Seeded

- **Table:** `admin_payment_settings`
- **Settings seeded:**
  | name | value |
  |------|-------|
  | `enable_stripe` | `on` |
  | `stripe_key` | `pk_test_MOCK_REPLACE_WITH_REAL_KEY` |
  | `stripe_secret` | `sk_test_MOCK_REPLACE_WITH_REAL_KEY` |
  | `currency` | `BRL` |

## What Needs to Be Done for Production

### Required — Real Stripe API Keys

1. Create a Stripe account at https://dashboard.stripe.com/
2. Get your **publishable key** (starts with `pk_live_` or `pk_test_`)
3. Get your **secret key** (starts with `sk_live_` or `sk_test_`)
4. Update `admin_payment_settings` table:
   ```sql
   UPDATE admin_payment_settings SET value = 'pk_live_YOUR_KEY' WHERE name = 'stripe_key';
   UPDATE admin_payment_settings SET value = 'sk_live_YOUR_KEY' WHERE name = 'stripe_secret';
   ```
5. Or use the admin panel: **Settings → Payment Settings → Stripe**

### Architecture Notes

- Stripe keys are **NOT** in `.env` — they're stored in the `admin_payment_settings` DB table
- The `StripePaymentController` reads them via `Utility::getAdminPaymentSetting()`
- Constants defined in `app/Config/Constants/SettingsConstants.php` (defaults to empty strings)
- Both `admin_payment_settings` and `company_payment_settings` tables exist for multi-tenant support
- Payment processing: `Stripe\Charge::create()` is used (legacy API — consider migrating to PaymentIntents)

### Stripe Routes

| Method | URI              | Action                                       |
| ------ | ---------------- | -------------------------------------------- |
| GET    | `/stripe/{code}` | Show payment form (code = encrypted plan_id) |
| POST   | `/stripe`        | Process Stripe charge                        |
| GET    | `/orders`        | List orders                                  |

### Webhook Setup (Future)

- No Stripe webhook endpoint is configured yet
- For production: Add `POST /stripe/webhook` endpoint
- Configure webhook signing secret in `admin_payment_settings`
