# Marketing Dashboard – Tier Update Suggestions

After the tier levels update (Silver $1.00, Gold $1.25, Platinum $1.50), the following was **already done** and **suggestions** for further consistency.

---

## Done in this pass

1. **Payments section – Commission Rate**
   - Replaced hardcoded “$1.00 per hour referred” with dynamic **tier label + rate**: e.g. “Silver Partner · $1.00/hr” (or Gold/Platinum and their rates).

2. **Payments section – “How Commission Works” card**
   - Replaced “Earn $1 for every hour of service” with:  
     **“Earn $X.XX/hr (Silver $1.00, Gold $1.25, Platinum $1.50)”** using the user’s current `displayCommissionRate`.

3. **Analytics – Summary header**
   - “Current Tier” now shows **tier + rate**, e.g. “Silver Partner · $1.00/hr”, instead of tier name only.

4. **Analytics – Quick Stats**
   - Added a **“Your Commission Rate”** row: “$X.XX/hr” and “(Silver)” / “(Gold)” / “(Platinum)” so the tier rate is visible in Analytics.

5. **`displayCommissionRate` computed**
   - When the backend returns 0 (no paid clients yet), the UI shows the **tier’s potential rate** (e.g. $1.00 for Silver) so the chip/cards never show “$0.00/hr”.

---

## Optional suggestions (not implemented)

- **Admin – Marketing Commissions tab**  
  Consider adding a **Tier** or **Rate** column (e.g. “Silver · $1.00/hr”) so admins see each partner’s tier when processing payments. Data can come from the same API that already returns tier/rate for staff management.

- **Dashboard “Previous Week Summary” / targets**  
  If you add tier-based targets (e.g. “Reach Gold at 6 paid clients”), surface them in the dashboard copy or a small info line so the tier ladder is clear.

- **Referral share copy**  
  Share buttons currently say “$3/hour discount”. If you ever want to mention partner earnings, you could add optional copy like “I earn commission when you book” (no need to show the exact rate).

- **My Clients table**  
  “My Clients” and “My Clients Performance” already show commission per client; no change needed for tiers unless you want a column like “Rate at time of booking” for history.

- **DashboardTemplate tier help modal**  
  Already shows Silver $1.00, Gold $1.25, Platinum $1.50 and client ranges; no update needed unless you change tier rules.

- **Backend / docs**  
  `MarketingTierService` and referral-code API already use the new tiers; ensure any internal or external docs (e.g. partner onboarding) list Silver / Gold / Platinum and the correct rates.

---

## Files touched

- `resources/js/components/MarketingDashboard.vue`  
  - Payments: dynamic commission rate; “How Commission Works” tier-aware text.  
  - Analytics: summary tier+rate; new Quick Stat “Your Commission Rate”; `displayCommissionRate` computed.

No changes were required in `DashboardTemplate.vue` (tier help modal) or `AdminMarketingStaffManagement.vue` (already tier-aware).
