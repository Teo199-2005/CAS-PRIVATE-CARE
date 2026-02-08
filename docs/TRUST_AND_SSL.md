# Trust & SSL Setup (ScamAdviser / site legitimacy)

This document explains what was done in the app to support trust signals and what **you** need to do on the server/hosting side (especially SSL) to improve how tools like ScamAdviser rate your site.

---

## What was done in the application (code)

- **Force HTTPS**  
  When `APP_ENV=production` and `APP_URL` starts with `https://`, the app:
  - Redirects HTTP → HTTPS (301) via `ForceHttps` middleware.
  - Forces all generated URLs to use `https` via `URL::forceScheme('https')`.

- **Config for business contact**  
  In `config/app.php`: `address`, `phone`, `email` (with env keys `APP_ADDRESS`, `APP_PHONE`, `APP_EMAIL`) so the footer and trust line use consistent, configurable data.

- **Crawlable trust line**  
  The landing footer includes a short, crawlable line stating that CAS Private Care LLC is a legitimate care marketplace with contact info.

- **robots.txt**  
  Removed non-standard `Crawl-delay` so crawlers (including trust-checking bots) are not unnecessarily delayed.

- **Existing SEO/trust**  
  The site already has: meta tags, JSON-LD (LocalBusiness), canonical URL, sitemap, and `robots: index, follow`. No change was needed there.

---

## What you must do on the server (required for better trust score)

### 1. Enable a valid SSL certificate (critical)

ScamAdviser and browsers treat **invalid or missing SSL** as a strong negative signal.

- **Option A – OVH (your current host)**  
  In the OVH control panel for the domain/server:
  - Use **OVH’s SSL** (if included) or **Let’s Encrypt** (free).
  - Ensure the certificate is active for `casprivatecare.online` (and `www.casprivatecare.online` if you use it).

- **Option B – Let’s Encrypt yourself (e.g. on VPS)**  
  If you have SSH access:
  - Install and run **Certbot** (or your distro’s package) for the web server (Apache/Nginx).
  - Obtain and auto-renew a certificate for `casprivatecare.online`.

After SSL is active, test:

- `https://casprivatecare.online` opens without certificate errors.
- The app already redirects HTTP → HTTPS in production when `APP_URL` is `https://...`.

### 2. Set production `.env` correctly

On the **production** server, in `.env`:

```env
APP_ENV=production
APP_URL=https://casprivatecare.online
```

Optional (used by footer and trust line):

```env
APP_ADDRESS="New York, USA"
APP_PHONE="+1 (646) 282-8282"
APP_EMAIL="contact@casprivatecare.online"
```

### 3. If the app is behind a reverse proxy (e.g. OVH load balancer)

So that `$request->secure()` and redirect work correctly, trust the proxy headers. In `bootstrap/app.php` (or where you register middleware), add Laravel’s `TrustProxies` and configure it for your provider (e.g. OVH). Laravel docs: [Trusting Proxies](https://laravel.com/docs/requests#configuring-trusted-proxies).

---

## Optional: Improve ScamAdviser / “is this a scam?” perception

- **Claim your site on ScamAdviser**  
  Use [ScamAdviser – Claim your website](https://www.scamadviser.com/claim-website/) to verify ownership and (where allowed) provide business details. This can improve how your site is presented.

- **Allow crawlers to read the site**  
  The app does not block bots by User-Agent. Once HTTPS is valid, ScamAdviser’s “content analysis” is more likely to succeed. If you add strict firewall or WAF rules later, allow known trust/safety crawlers.

- **WHOIS privacy**  
  Keeping WHOIS privacy is fine for privacy; ScamAdviser may still rate “hidden identity” slightly lower. Claiming the site and having valid SSL matter more.

---

## Quick checklist

| Step | Where | Status |
|------|--------|--------|
| SSL certificate valid for casprivatecare.online | Hosting (OVH / server) | ☐ You do this |
| APP_URL=https://casprivatecare.online | Production `.env` | ☐ You do this |
| APP_ENV=production | Production `.env` | ☐ You do this |
| HTTP → HTTPS redirect | App (done) | ✓ |
| Trust/contact config | App (done) | ✓ |
| Claim site on ScamAdviser | scamadviser.com | ☐ Optional |

After SSL is valid and `APP_URL` is set to `https://casprivatecare.online`, re-check your site on ScamAdviser after a few days; their crawler may need to re-run.
