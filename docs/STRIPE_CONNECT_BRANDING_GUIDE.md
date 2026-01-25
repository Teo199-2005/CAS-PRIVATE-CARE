# 🎨 STRIPE CONNECT BRANDING CUSTOMIZATION GUIDE

## Your CAS Private Care Brand Colors

From your landing page, these are your official brand colors:

### Primary Colors:
- **Brand Blue**: `#0B4FA2` (main brand color)
- **Primary Blue**: `#3b82f6` (buttons, gradients)
- **Dark Blue**: `#1e40af` (gradient end)
- **Orange**: `#f97316` → `#ea580c` (accent)
- **Green**: `#10b981` → `#059669` (success)

### Logo Files:
- **Main Logo**: `/public/logo.png`
- **Icon Logo**: `/public/logo flower.png`

---

## ✅ WHAT WE'VE DONE (Code Updates)

### 1. Updated `StripePaymentService.php`
- Added branding settings to Connect account creation
- Set your logo URL: `url('/logo.png')`
- Set primary color: `#3b82f6`
- Set secondary color: `#0B4FA2`

### 2. Backend Changes Applied:
```php
'settings' => [
    'branding' => [
        'icon' => url('/logo.png'),
        'logo' => url('/logo.png'),
        'primary_color' => '#3b82f6',
        'secondary_color' => '#0B4FA2',
    ],
],
```

---

## 🎯 NEXT STEP: Configure Stripe Dashboard Branding

### Why You Need This:
The Stripe Connect onboarding page is hosted by Stripe, not your server. To customize it with YOUR logo and colors, you need to set up branding in your **Stripe Dashboard**.

### Step-by-Step Instructions:

#### 1. Login to Stripe Dashboard
Go to: https://dashboard.stripe.com/

#### 2. Navigate to Connect Settings
- Click **Settings** (top right gear icon)
- Click **Connect** in the left sidebar
- Click **Branding** tab

#### 3. Upload Your Logo
**Option A: Use Your Existing Logo**
- Click "Upload logo"
- Select `public/logo.png` from your project
- Recommended size: 512x512px (square format works best)

**Option B: Use Your Icon**
- Alternative: Upload `public/logo flower.png`

#### 4. Set Brand Colors

**Brand Color (Primary):**
```
#3b82f6
```
This is the main color for your onboarding flow.

**Icon Color:**
```
#0B4FA2
```
This matches your main brand blue.

**Accent Color (Optional):**
```
#10b981
```
For success states and positive actions.

#### 5. Customize Text (Optional)

**Platform Name:**
```
CAS Private Care
```

**Business URL:**
```
https://casprivatecare.com
```

**Support URL:**
```
https://casprivatecare.com/support
```

**Privacy Policy URL:**
```
https://casprivatecare.com/privacy
```

#### 6. Preview and Save
- Click "Preview" to see how it looks
- Click "Save changes"

---

## 🖼️ What Your Branded Page Will Look Like

### Before (Stripe Default):
- ❌ Generic "Casprivate care" text
- ❌ Purple/blue Stripe colors (#6772e5)
- ❌ No logo
- ❌ Generic Stripe branding

### After (Your Branding):
- ✅ **Your logo** (CAS Private Care logo)
- ✅ **Your colors** (#3b82f6, #0B4FA2)
- ✅ **Your company name** (CAS Private Care)
- ✅ Professional, branded experience

---

## 🔧 Account-Level Branding (Advanced)

The code we added will also customize individual account onboarding, but **platform-level branding** (Stripe Dashboard settings) takes precedence and is easier to manage.

### What the Code Does:
- Sets logo URL for each Connect account
- Sets colors programmatically
- Good for dynamic branding per account

### What Dashboard Settings Do:
- Global branding for ALL Connect accounts
- Shows your logo immediately
- Easier to update (no code changes needed)

---

## 📝 Testing Your Branding

### Before Testing:
1. ✅ Configure Stripe Dashboard branding (above steps)
2. ✅ Save all settings
3. ✅ Clear your browser cache

### Test Flow:
1. Login as caregiver: `caregiver@demo.com`
2. Go to **Payment Information** section
3. Click **"Connect Payout Method"**
4. You should now see:
   - Your CAS Private Care logo
   - Your blue color scheme (#3b82f6)
   - "CAS Private Care partners with Stripe..." text

### Expected Result:
```
┌─────────────────────────────────────────┐
│  [CAS Logo] CAS Private Care            │  ← Your logo & name
│                                          │
│  CAS Private Care partners with Stripe  │  ← Your company name
│  for secure payments and financial      │
│  services.                               │
│                                          │
│  [Your Blue Color]                       │  ← #3b82f6
│                                          │
│  Let's get started                       │
│  Fill out a few details below.           │
│                                          │
│  [Submit Button - Your Blue Color]       │  ← #3b82f6
└─────────────────────────────────────────┘
```

---

## 🚨 Troubleshooting

### Issue: Still seeing purple Stripe colors
**Solution**: 
- Clear browser cache (Ctrl+Shift+Delete)
- Try incognito/private browsing
- Wait 5-10 minutes for Stripe cache to clear

### Issue: Logo not showing
**Solution**:
- Make sure logo file is publicly accessible: `http://127.0.0.1:8000/logo.png`
- Upload logo directly in Stripe Dashboard instead
- Use square format (512x512px recommended)

### Issue: "Casprivate care" still showing (not "CAS Private Care")
**Solution**:
- Update Platform Name in Stripe Dashboard → Connect → Branding
- Set to: "CAS Private Care" (with proper spacing)

### Issue: Colors not matching exactly
**Solution**:
- Use hex codes exactly: `#3b82f6` (no spaces)
- Don't use RGB or color names
- Stripe may slightly adjust colors for accessibility

---

## 🎨 Color Reference Sheet

For easy copy-paste when configuring Stripe:

| Element | Hex Code | Usage |
|---------|----------|-------|
| **Primary Blue** | `#3b82f6` | Main brand color, buttons |
| **Brand Blue** | `#0B4FA2` | Secondary elements |
| **Dark Blue** | `#1e40af` | Hover states |
| **Green** | `#10b981` | Success messages |
| **Orange** | `#f97316` | Warnings, highlights |

---

## 📞 Need Help?

If you're having trouble with branding:

1. **Check Stripe Dashboard**: Settings → Connect → Branding
2. **Verify Logo URL**: `http://127.0.0.1:8000/logo.png` should load
3. **Clear Cache**: Browser and Stripe cache (wait 10 min)
4. **Test in Incognito**: Rules out browser cache issues

---

## ✨ Final Result

Once configured, your Stripe Connect onboarding will look:
- ✅ Professional and branded
- ✅ Match your landing page colors
- ✅ Show your logo prominently
- ✅ Trustworthy and cohesive with your site

**The page is still hosted and secured by Stripe**, but it now looks like YOUR platform! 🎉

