<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>Job Listings - CAS Private Care</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .filter-group select, .filter-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .clients-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        .client-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .client-header { display: flex; justify-content: between; align-items: center; margin-bottom: 15px; }
        .client-avatar { width: 50px; height: 50px; border-radius: 50%; background: #007bff; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .client-info { flex: 1; margin-left: 15px; }
        .client-name { font-weight: bold; font-size: 16px; }
        .client-age { color: #666; font-size: 14px; }
        .service-details { margin-bottom: 15px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .detail-label { color: #666; }
        .detail-value { font-weight: 500; }
        .pay-rate { background: #28a745; color: white; padding: 4px 8px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .urgency { background: #dc3545; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .urgency.scheduled { background: #6c757d; }
        .urgency.today { background: #fd7e14; }
        .btn-apply { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-weight: bold; }
        .btn-apply:hover { background: #0056b3; }
        .distance { color: #28a745; font-size: 12px; }
        .no-clients { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Job Listings</h1>
            <p>Find clients that match your skills and availability</p>
            <!-- ...existing filters code... -->
        </div>

        <div class="clients-grid" id="clients-container">
            @forelse($clients as $client)
                <div class="client-card">
                    <div class="client-header">
                        @if($client['avatar'])
                            <img src="{{ $client['avatar'] }}" alt="{{ $client['name'] }}" class="client-avatar" style="object-fit:cover; width:50px; height:50px; border-radius:50%;" loading="lazy" decoding="async" />
                        @else
                            <div class="client-avatar">{{ $client['initials'] }}</div>
                        @endif
                        <div class="client-info">
                            <div class="client-name">{{ $client['name'] }}</div>
                            <div class="client-age">Age {{ $client['age'] }} • {{ ucfirst($client['mobilityLevel']) }}</div>
                        </div>
                        <div class="pay-rate">{{ $client['payRate'] }}</div>
                    </div>
                    <div class="service-details">
                        <div class="detail-row">
                            <span class="detail-label">Service:</span>
                            <span class="detail-value">{{ $client['careType'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value">{{ $client['serviceDate'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Time:</span>
                            <span class="detail-value">{{ $client['startTime'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value">{{ $client['location'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Duration:</span>
                            <span class="detail-value">{{ $client['duration'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Urgency:</span>
                            <span class="urgency {{ $client['urgency'] }}">{{ ucfirst($client['urgency']) }}</span>
                        </div>
                    </div>
                    <button class="btn-apply">Apply for This Client</button>
                </div>
            @empty
                <div class="no-clients">No clients available at this time.</div>
            @endforelse
        </div>
    </div>

    <script>
        // Simple filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filters = {
                search: document.getElementById('search'),
                borough: document.getElementById('borough'),
                service_type: document.getElementById('service_type'),
                pay_rate: document.getElementById('pay_rate'),
                availability: document.getElementById('availability')
            };

            Object.values(filters).forEach(filter => {
                filter.addEventListener('change', filterClients);
                filter.addEventListener('input', filterClients);
            });

            function filterClients() {
                const cards = document.querySelectorAll('.client-card');
                cards.forEach(card => {
                    card.style.display = 'block';
                });
            }

            // Apply button functionality
            document.querySelectorAll('.btn-apply').forEach(btn => {
                btn.addEventListener('click', function() {
                    alert('Application submitted! The client will be notified of your interest.');
                });
            });
        });
    </script>
</body>
</html>