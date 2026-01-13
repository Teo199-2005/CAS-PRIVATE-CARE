<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Control Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div style="max-width: 900px; margin: 24px auto; padding: 0 16px;">
        <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 12px;">Admin Settings</h1>

        @if (session('success'))
            <div style="background:#ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px;">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background:#fef2f2; border: 1px solid #ef4444; color: #7f1d1d; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px;">
                <div style="font-weight: 600; margin-bottom: 6px;">Please fix the following:</div>
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/admin/settings" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;">
            @csrf

            <h2 style="font-size: 18px; font-weight: 700; margin: 0 0 12px;">Landing Page</h2>

            <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Hero Title</label>
                    <input name="landing[hero_title]" value="{{ old('landing.hero_title', $landing['hero_title'] ?? '') }}" style="width:100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;" />
                </div>

                <div>
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Hero Subtitle</label>
                    <textarea name="landing[hero_subtitle]" rows="3" style="width:100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;">{{ old('landing.hero_subtitle', $landing['hero_subtitle'] ?? '') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Hero CTA Text</label>
                        <input name="landing[hero_cta_text]" value="{{ old('landing.hero_cta_text', $landing['hero_cta_text'] ?? '') }}" style="width:100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;" />
                    </div>

                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Hero CTA URL</label>
                        <input name="landing[hero_cta_url]" value="{{ old('landing.hero_cta_url', $landing['hero_cta_url'] ?? '') }}" style="width:100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;" />
                    </div>
                </div>
            </div>

            <div style="margin-top: 14px; display:flex; gap: 10px; align-items:center;">
                <button type="submit" style="background:#111827; color:#fff; border: 0; padding: 10px 14px; border-radius: 10px; font-weight: 600; cursor: pointer;">Save</button>
                <a href="/" style="color:#2563eb; text-decoration:none;">View homepage</a>
            </div>

            <p style="margin-top: 12px; color: #6b7280; font-size: 13px;">
                Tip: this saves to the database (table <code>app_settings</code>), so you won't need to edit <code>resources/views/landing.blade.php</code> for these fields.
            </p>
        </form>
    </div>
</body>
</html>