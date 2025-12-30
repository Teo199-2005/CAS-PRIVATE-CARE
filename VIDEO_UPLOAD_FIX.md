# Fix Video Not Showing on Website

## Issue Identified
The video `what.mp4` exists locally in your `public` folder but hasn't been uploaded to your server.

**Location in code:** `resources/views/landing.blade.php` line 2603
```html
<source src="{{ asset('what.mp4') }}" type="video/mp4">
```

## Solution: Upload Video to Server

You have 3 options:

---

## Option 1: Upload via Git (RECOMMENDED)

### Step 1: Add video to Git
On your **local machine** (Windows PowerShell):

```powershell
cd "C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)"

# Add the video file
git add public/what.mp4

# Commit
git commit -m "Add what.mp4 video to landing page"

# Push to GitHub
git push origin master
```

### Step 2: Pull on Server
SSH into your server and run:

```bash
cd /var/www/cas-private-care
git pull origin master
sudo systemctl restart apache2
```

---

## Option 2: Upload via SCP (Direct Upload)

On your **local machine** (Windows PowerShell):

```powershell
# Navigate to where the video is
cd "C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\public"

# Upload using SCP
scp what.mp4 ubuntu@15.204.248.209:/tmp/

# Then SSH in and move it
ssh ubuntu@15.204.248.209
sudo mv /tmp/what.mp4 /var/www/cas-private-care/public/
sudo chown www-data:www-data /var/www/cas-private-care/public/what.mp4
sudo chmod 644 /var/www/cas-private-care/public/what.mp4
```

---

## Option 3: Upload via SFTP (GUI Method)

1. Use **WinSCP** or **FileZilla**
2. Connect to: `15.204.248.209`
3. Username: `ubuntu`
4. Navigate to: `/var/www/cas-private-care/public/`
5. Upload `what.mp4` from your local `public` folder
6. Set permissions:
   - Owner: `www-data`
   - Group: `www-data`
   - Permissions: `644`

---

## Verification Steps

### 1. Check if file exists on server:
```bash
ssh ubuntu@15.204.248.209
ls -lh /var/www/cas-private-care/public/what.mp4
```

Expected output:
```
-rw-r--r-- 1 www-data www-data 5.2M Dec 30 15:00 what.mp4
```

### 2. Check file permissions:
```bash
# Should be readable by web server
stat /var/www/cas-private-care/public/what.mp4
```

### 3. Test video URL:
Open browser and go to:
```
http://15.204.248.209/what.mp4
```

You should see the video or it should download. If you get 404, the file isn't there.

---

## Quick Fix Commands (Option 1 - Git Method)

### On Your Local Machine:
```powershell
cd "C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)"
git add public/what.mp4
git commit -m "Add what.mp4 video for landing page"
git push origin master
```

### On Your Server:
```bash
cd /var/www/cas-private-care
git pull origin master
sudo chown www-data:www-data public/what.mp4
sudo chmod 644 public/what.mp4
sudo systemctl restart apache2
```

---

## Troubleshooting

### Video shows 404:
```bash
# Check if file exists
ls -la /var/www/cas-private-care/public/what.mp4

# Check Apache document root
sudo nano /etc/apache2/sites-available/000-default.conf
# Should point to: /var/www/cas-private-care/public
```

### Video won't play:
```bash
# Check MIME types
sudo nano /etc/apache2/mods-available/mime.conf
# Should have: video/mp4 mp4

# Restart Apache
sudo systemctl restart apache2
```

### Permission denied:
```bash
# Fix ownership
sudo chown www-data:www-data /var/www/cas-private-care/public/what.mp4

# Fix permissions (644 = readable by all, writable by owner)
sudo chmod 644 /var/www/cas-private-care/public/what.mp4
```

### Video too large (causes timeout):
```bash
# Check video file size
ls -lh /var/www/cas-private-care/public/what.mp4

# If > 10MB, optimize it or increase Apache limits
sudo nano /etc/apache2/apache2.conf
# Add: LimitRequestBody 52428800  # 50MB
```

---

## Video Optimization (Optional)

If video is too large, compress it:

### On Windows (using FFmpeg):
```powershell
# Install FFmpeg first: winget install ffmpeg

# Compress video
ffmpeg -i what.mp4 -vcodec h264 -acodec aac -strict -2 -crf 28 what_optimized.mp4
```

### On Server:
```bash
sudo apt install ffmpeg -y
cd /var/www/cas-private-care/public
ffmpeg -i what.mp4 -vcodec h264 -acodec aac -strict -2 -crf 28 what_optimized.mp4
mv what_optimized.mp4 what.mp4
```

---

## Check Current Video Code

The video is in: `resources/views/landing.blade.php`

```html
<video autoplay loop muted playsinline preload="auto" 
       style="width: 100%; height: 500px; object-fit: cover; 
              border-radius: 20px; box-shadow: 0 20px 60px rgba(59, 130, 246, 0.2);">
    <source src="{{ asset('what.mp4') }}" type="video/mp4">
    Your browser does not support the video tag.
</video>
```

The `{{ asset('what.mp4') }}` will generate URL: `http://yourdomain.com/what.mp4`

---

## Expected File Structure on Server:

```
/var/www/cas-private-care/
├── public/
│   ├── what.mp4          ← Video should be here
│   ├── logo.png
│   ├── logo flower.png
│   └── cover.jpg
```

---

## Quick Test

After uploading, test by visiting:
```
http://15.204.248.209/what.mp4
```

If you see/download the video → Video is uploaded correctly!
If you get 404 → Video is missing from server

---

## Recommended: Use Option 1 (Git)

**Pros:**
- ✅ Version controlled
- ✅ Easy to replicate on other servers
- ✅ Automatic backup on GitHub
- ✅ Easy to rollback if needed

**Cons:**
- ⚠️ GitHub has file size limits (100MB, 50MB warning)
- ⚠️ Makes repository larger

If your video is **> 50MB**, use Option 2 (SCP) instead.

---

## File Size Check

Check your video size:
```powershell
Get-Item "C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\public\what.mp4" | Select-Object Name, @{Name="Size(MB)";Expression={[math]::Round($_.Length/1MB,2)}}
```

- **< 50MB**: Use Git (Option 1) ✅
- **> 50MB**: Use SCP (Option 2) ✅

---

## Summary

**The video isn't showing because it's only on your local machine, not on the server!**

**Fastest Fix:**
1. Run Git commands on local machine (add, commit, push)
2. SSH into server and run `git pull origin master`
3. Set permissions: `sudo chown www-data:www-data public/what.mp4`
4. Test: `http://15.204.248.209/what.mp4`

That's it! 🎬
