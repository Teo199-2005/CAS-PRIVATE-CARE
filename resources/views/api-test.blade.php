<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>API Direct Test</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        .test-section { margin: 30px 0; padding: 25px; border: 2px solid #e0e0e0; border-radius: 6px; background: #fafafa; }
        .test-section h2 { color: #34495e; margin-top: 0; }
        button { padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
        button:hover { background: #2980b9; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        button:active { transform: translateY(0); }
        .result { margin-top: 20px; padding: 15px; background: white; border-radius: 4px; border-left: 4px solid #3498db; }
        .success { border-left-color: #27ae60; background: #e8f8f5; }
        .error { border-left-color: #e74c3c; background: #f8e8e8; }
        .warning { border-left-color: #f39c12; background: #fef5e7; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; overflow-x: auto; border-radius: 4px; font-size: 12px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-left: 10px; }
        .status.ok { background: #27ae60; color: white; }
        .status.fail { background: #e74c3c; color: white; }
        .loading { display: inline-block; padding: 8px 16px; background: #f39c12; color: white; border-radius: 4px; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Admin API Direct Test</h1>
        <p>Testing API endpoints directly from browser to diagnose "No data available" issue</p>

        <div class="test-section">
            <h2>Test 1: /api/admin/users <span id="users-status"></span></h2>
            <button onclick="testUsersAPI()">🚀 Run Test</button>
            <div id="users-result"></div>
        </div>

        <div class="test-section">
            <h2>Test 2: /api/admin/bookings <span id="bookings-status"></span></h2>
            <button onclick="testBookingsAPI()">🚀 Run Test</button>
            <div id="bookings-result"></div>
        </div>

        <div class="test-section">
            <h2>Test 3: Authentication Check <span id="auth-status"></span></h2>
            <button onclick="testAuth()">🚀 Run Test</button>
            <div id="auth-result"></div>
        </div>

        <div class="test-section">
            <h2>Test 4: Network Inspector</h2>
            <p>Open browser DevTools (F12) → Network tab → Run tests above → Check for failed requests</p>
            <button onclick="alert('Press F12 now, go to Network tab, then run the tests above')">ℹ️ Show Instructions</button>
        </div>
    </div>

    <script>
        async function testUsersAPI() {
            const statusEl = document.getElementById('users-status');
            const resultDiv = document.getElementById('users-result');
            
            statusEl.innerHTML = '<span class="loading">Testing...</span>';
            resultDiv.innerHTML = '';
            
            try {
                console.log('Calling /api/admin/users...');
                
                const response = await fetch('/api/admin/users', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const data = await response.json();
                console.log('Response data:', data);
                
                let html = '<div class="result ' + (response.ok ? 'success' : 'error') + '">';
                html += `<p><strong>HTTP Status:</strong> ${response.status} ${response.statusText}</p>`;
                
                if (!response.ok) {
                    statusEl.innerHTML = '<span class="status fail">FAILED</span>';
                    html += `<p><strong>❌ Error:</strong> ${data.error || data.message || 'Unknown error'}</p>`;
                    if (response.status === 401) {
                        html += `<p><strong>Reason:</strong> Not authenticated. Try logging in again.</p>`;
                    } else if (response.status === 403) {
                        html += `<p><strong>Reason:</strong> Not authorized. Check if you're logged in as admin.</p>`;
                    }
                } else {
                    statusEl.innerHTML = '<span class="status ok">PASSED</span>';
                    html += `<p><strong>✅ Total Users:</strong> ${data.users?.length || 0}</p>`;
                    
                    if (data.users && data.users.length > 0) {
                        const caregivers = data.users.filter(u => u.type === 'Caregiver');
                        const clients = data.users.filter(u => u.type === 'Client');
                        const admins = data.users.filter(u => u.type === 'Admin');
                        
                        html += `<p><strong>Breakdown:</strong></p>`;
                        html += `<ul>`;
                        html += `<li>Caregivers: ${caregivers.length}</li>`;
                        html += `<li>Clients: ${clients.length}</li>`;
                        html += `<li>Admins: ${admins.length}</li>`;
                        html += `</ul>`;
                        
                        html += `<p><strong>Sample Data (first user):</strong></p>`;
                        html += `<pre>${JSON.stringify(data.users[0], null, 2)}</pre>`;
                    } else {
                        html += `<p class="warning">⚠️ API returned but no users in array</p>`;
                    }
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
                
            } catch (error) {
                console.error('Error:', error);
                statusEl.innerHTML = '<span class="status fail">ERROR</span>';
                resultDiv.innerHTML = `
                    <div class="result error">
                        <p><strong>❌ JavaScript Error:</strong> ${error.message}</p>
                        <p>Check browser console (F12) for details</p>
                        <pre>${error.stack}</pre>
                    </div>
                `;
            }
        }

        async function testBookingsAPI() {
            const statusEl = document.getElementById('bookings-status');
            const resultDiv = document.getElementById('bookings-result');
            
            statusEl.innerHTML = '<span class="loading">Testing...</span>';
            resultDiv.innerHTML = '';
            
            try {
                console.log('Calling /api/admin/bookings...');
                
                const response = await fetch('/api/admin/bookings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                console.log('Response:', data);
                
                let html = '<div class="result ' + (response.ok ? 'success' : 'error') + '">';
                html += `<p><strong>HTTP Status:</strong> ${response.status} ${response.statusText}</p>`;
                
                if (!response.ok) {
                    statusEl.innerHTML = '<span class="status fail">FAILED</span>';
                    html += `<p><strong>❌ Error:</strong> ${data.error || data.message || 'Unknown error'}</p>`;
                } else {
                    statusEl.innerHTML = '<span class="status ok">PASSED</span>';
                    html += `<p><strong>✅ Success:</strong> ${data.success ? 'Yes' : 'No'}</p>`;
                    html += `<p><strong>Total Bookings:</strong> ${data.data?.length || 0}</p>`;
                    
                    if (data.data && data.data.length > 0) {
                        html += `<p><strong>Sample Booking:</strong></p>`;
                        html += `<pre>${JSON.stringify(data.data[0], null, 2)}</pre>`;
                    }
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
                
            } catch (error) {
                console.error('Error:', error);
                statusEl.innerHTML = '<span class="status fail">ERROR</span>';
                resultDiv.innerHTML = `
                    <div class="result error">
                        <p><strong>❌ JavaScript Error:</strong> ${error.message}</p>
                        <pre>${error.stack}</pre>
                    </div>
                `;
            }
        }

        async function testAuth() {
            const statusEl = document.getElementById('auth-status');
            const resultDiv = document.getElementById('auth-result');
            
            statusEl.innerHTML = '<span class="loading">Testing...</span>';
            resultDiv.innerHTML = '';
            
            try {
                const response = await fetch('/api/profile?user_type=admin', {
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                let html = '<div class="result ' + (response.ok ? 'success' : 'error') + '">';
                html += `<p><strong>HTTP Status:</strong> ${response.status}</p>`;
                html += `<p><strong>Authenticated:</strong> ${response.ok ? '✅ Yes' : '❌ No'}</p>`;
                
                if (response.ok && data.user) {
                    statusEl.innerHTML = '<span class="status ok">LOGGED IN</span>';
                    html += `<p><strong>User:</strong> ${data.user.name}</p>`;
                    html += `<p><strong>Email:</strong> ${data.user.email}</p>`;
                    html += `<p><strong>Type:</strong> ${data.user.user_type}</p>`;
                } else {
                    statusEl.innerHTML = '<span class="status fail">NOT LOGGED IN</span>';
                    html += `<p>⚠️ You need to log in to admin dashboard first</p>`;
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
                
            } catch (error) {
                statusEl.innerHTML = '<span class="status fail">ERROR</span>';
                resultDiv.innerHTML = `<div class="result error"><p>❌ ${error.message}</p></div>`;
            }
        }

        // Auto-run auth check on page load
        window.addEventListener('load', () => {
            console.log('Page loaded. Running authentication check...');
            testAuth();
        });
    </script>
</body>
</html>
