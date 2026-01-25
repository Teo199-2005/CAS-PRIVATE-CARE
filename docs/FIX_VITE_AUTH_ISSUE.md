# Fix Vite Dev Server Authentication Issue

## 🔴 The Problem

You're seeing this error:
```
Failed to load resource: the server responded with a status of 401 (Unauthorized)
URL: http://[::1]:5173/resources/js/components/ConnectPaymentMethod.vue
```

The `[::1]:5173` URL indicates **Vite dev server is running**. This causes authentication issues because:

1. **Laravel runs on port 8000** → Has your session cookies
2. **Vite dev runs on port 5173** → Different origin, no session cookies
3. **API calls fail** → 401 Unauthorized (can't read session from different port)

---

## ✅ The Solution

### Option 1: Stop Vite Dev Server (Recommended)

**Step 1: Find the terminal running Vite**
Look for a terminal with output like:
```
VITE v7.3.0  ready in 234 ms
➜  Local:   http://localhost:5173/
```

**Step 2: Stop it**
- Press `Ctrl+C` in that terminal

**Step 3: Restart Laravel**
```powershell
php artisan serve
```

**Step 4: Clear browser cache**
- Press `Ctrl+Shift+R` to hard refresh
- Or use incognito window

**Step 5: Test**
- Go to: http://127.0.0.1:8000/client-dashboard
- Click "Link Your Payment Method"
- Should work now! ✅

---

### Option 2: Configure Vite for Laravel Auth (Advanced)

If you want to keep using dev server, update `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        // THIS IS THE KEY FIX
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
        },
        // Allow cookies from Laravel
        cors: {
            origin: 'http://127.0.0.1:8000',
            credentials: true,
        },
    },
});
```

Then restart Vite dev server.

---

## 🎯 Recommended Approach

**For production-like testing, use built assets:**

1. **Stop Vite dev** (if running)
2. **Build once**:
   ```powershell
   npm run build
   ```
3. **Use Laravel only**:
   ```powershell
   php artisan serve
   ```
4. **Access via Laravel port only**: http://127.0.0.1:8000

**Benefits:**
- ✅ Matches production environment
- ✅ No CORS issues
- ✅ Session cookies work perfectly
- ✅ Faster page loads (no HMR overhead)
- ✅ Tests actual deployment setup

---

## 🔍 How to Check Which Mode You're In

### In Browser Console (F12):
```javascript
// Check current URL
console.log('Current origin:', window.location.origin);

// If shows http://localhost:5173 → Using Vite dev
// If shows http://127.0.0.1:8000 → Using Laravel (correct!)
```

### Check Network Tab (F12):
Look at any request's URL:
- ✅ `http://127.0.0.1:8000/api/...` → Good!
- ❌ `http://[::1]:5173/api/...` → Bad! (Vite dev)

---

## 🐛 Troubleshooting

### Still seeing 401 after stopping Vite?

**Clear browser cache:**
```
1. Press Ctrl+Shift+Delete
2. Select "Cached images and files"
3. Click "Clear data"
```

**Or use incognito window:**
- Chrome: `Ctrl+Shift+N`
- Firefox: `Ctrl+Shift+P`

### How to kill all Node/Vite processes:

**Windows PowerShell:**
```powershell
# Kill all node processes
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force

# Then restart Laravel
php artisan serve
```

**Then rebuild:**
```powershell
npm run build
```

### Verify Vite is stopped:

**Check for running Node processes:**
```powershell
Get-Process | Where-Object {$_.ProcessName -eq "node"} | Select-Object Id, ProcessName, StartTime
```

**Check if port 5173 is free:**
```powershell
netstat -ano | findstr :5173
```

If empty → Vite is stopped ✅

---

## ✅ Success Checklist

After stopping Vite dev server:

- [ ] No Node processes running (or only background ones)
- [ ] Port 5173 is free (netstat shows nothing)
- [ ] Laravel server running on port 8000
- [ ] Assets built (`npm run build` completed)
- [ ] Browser cache cleared
- [ ] Dashboard loads from http://127.0.0.1:8000
- [ ] Network tab shows requests to port 8000 (not 5173)
- [ ] Click "Link Your Payment Method" works
- [ ] No 401 errors in console
- [ ] Stripe form loads successfully

---

## 🚀 Quick Fix Commands

**Stop everything and restart clean:**

```powershell
# 1. Kill all node processes
Get-Process node -ErrorAction SilentlyContinue | Stop-Process -Force

# 2. Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Rebuild assets
npm run build

# 4. Start Laravel
php artisan serve
```

Then open: http://127.0.0.1:8000/client-dashboard

---

## 💡 Development Workflow

**During development:**
- Use Vite dev server: `npm run dev` (faster hot-reload)
- But **logout/login flow won't work** across ports

**For testing auth features:**
- Stop Vite: `Ctrl+C`
- Build: `npm run build`
- Use Laravel only: `php artisan serve`
- **Auth works perfectly** ✅

**Before deploying:**
- Always test with built assets
- Never deploy with `npm run dev` running

---

## 📞 Still Having Issues?

### Check Laravel logs:
```powershell
Get-Content 'storage\logs\laravel.log' -Tail 20
```

### Check if session is being sent:
In browser console:
```javascript
fetch('/api/profile')
  .then(r => r.json())
  .then(d => console.log('User:', d))
  .catch(e => console.error('Not authenticated:', e));
```

### Verify session cookie exists:
```javascript
console.log('Cookies:', document.cookie);
// Should see: laravel_session=...
```

---

**Last Updated:** January 9, 2026  
**Issue:** Vite dev server (port 5173) breaks Laravel session authentication  
**Solution:** Stop Vite, use built assets, access via port 8000 only
