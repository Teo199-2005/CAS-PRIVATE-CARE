<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Service - CAS Private Care</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        .form-group.full-width { flex: 100%; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .btn-primary { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-primary:hover { background: #0056b3; }
        .btn-demo { background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-right: 10px; }
        .btn-demo:hover { background: #218838; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h1 style="text-align: center; margin-bottom: 30px; color: #333;">Book a Service</h1>
            
            <form action="/bookings" method="POST">
                @csrf
                
                <!-- Service Details -->
                <div class="form-section">
                    <div class="section-title">Service Details</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Service Type</label>
                            <select name="service_type" required>
                                <option value="">Select service type</option>
                                <option value="personal_care">Personal Care</option>
                                <option value="companionship">Companionship</option>
                                <option value="medical_assistance">Medical Assistance</option>
                                <option value="housekeeping">Housekeeping</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duty Type</label>
                            <select name="duty_type" required>
                                <option value="">Select duty type</option>
                                <option value="live_in">Live-in Care</option>
                                <option value="hourly">Hourly Care</option>
                                <option value="overnight">Overnight Care</option>
                                <option value="respite">Respite Care</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Service Date</label>
                            <input type="date" name="service_date" required>
                        </div>
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="time" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="time" name="end_time" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Duration (Days)</label>
                            <select name="duration_days">
                                <option value="1">1 Day</option>
                                <option value="7">1 Week</option>
                                <option value="15" selected>15 Days</option>
                                <option value="30">1 Month</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Service Days</label>
                            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px;">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="monday" id="monday">
                                    <label for="monday">Monday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="tuesday" id="tuesday">
                                    <label for="tuesday">Tuesday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="wednesday" id="wednesday">
                                    <label for="wednesday">Wednesday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="thursday" id="thursday">
                                    <label for="thursday">Thursday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="friday" id="friday">
                                    <label for="friday">Friday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="saturday" id="saturday">
                                    <label for="saturday">Saturday</label>
                                </div>
                                <div class="checkbox-group">
                                    <input type="checkbox" name="service_days[]" value="sunday" id="sunday">
                                    <label for="sunday">Sunday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-section">
                    <div class="section-title">Location</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>County</label>
                            <select name="county" id="booking-county-select" required>
                                <option value="">Select County</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>City/Borough</label>
                            <select name="city" id="booking-city-select" disabled>
                                <option value="">Select County First</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Street Address</label>
                            <input type="text" name="street_address" required>
                        </div>
                        <div class="form-group">
                            <label>Apartment/Unit Number</label>
                            <input type="text" name="apartment_unit">
                        </div>
                    </div>
                </div>

                <!-- Caregiver Preferences -->
                <div class="form-section">
                    <div class="section-title">Caregiver Preferences</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender Preference</label>
                            <select name="gender_preference">
                                <option value="no_preference">No Preference</option>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Language Preference</label>
                            <select name="language_preference">
                                <option value="english">English</option>
                                <option value="spanish">Spanish</option>
                                <option value="french">French</option>
                                <option value="mandarin">Mandarin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Background Check Level</label>
                            <select name="background_check_level">
                                <option value="standard">Standard</option>
                                <option value="enhanced">Enhanced</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Budget & Payment -->
                <div class="form-section">
                    <div class="section-title">Budget & Payment</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Hourly Rate ($)</label>
                            <input type="number" name="hourly_rate" step="0.01" min="15" max="100">
                        </div>
                        <div class="form-group">
                            <label>Total Budget ($)</label>
                            <input type="number" name="total_budget" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="insurance">Insurance</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div class="form-section">
                    <div class="section-title">Client Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Client Age</label>
                            <input type="number" name="client_age" min="18" max="120">
                        </div>
                        <div class="form-group">
                            <label>Mobility Level</label>
                            <select name="mobility_level">
                                <option value="">Select mobility level</option>
                                <option value="independent">Independent</option>
                                <option value="assisted">Assisted</option>
                                <option value="wheelchair">Wheelchair</option>
                                <option value="bedridden">Bedridden</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Urgency Level</label>
                            <select name="urgency_level">
                                <option value="scheduled">Scheduled</option>
                                <option value="within_24h">Within 24 Hours</option>
                                <option value="today">Today</option>
                                <option value="asap">ASAP</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="form-section">
                    <div class="section-title">Additional Options</div>
                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="transportation_needed" id="transport">
                                <label for="transport">Transportation needed</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" name="recurring_service" id="recurring">
                                <label for="recurring">Recurring service</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Recurring Schedule</label>
                            <select name="recurring_schedule">
                                <option value="">Select schedule</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="bi_weekly">Bi-weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div class="form-section">
                    <div class="section-title">Special Instructions</div>
                    <div class="form-group full-width">
                        <textarea name="special_instructions" placeholder="Any special requirements or instructions..."></textarea>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" class="btn-demo" onclick="fillDemoData()">🎲 Demo Fill</button>
                    <button type="submit" class="btn-primary">Book Service</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // NY Location Data for Booking Form
    let bookingLocationData = {};
    
    // Load location data
    document.addEventListener('DOMContentLoaded', async function() {
        await loadBookingLocationData();
    });

    async function loadBookingLocationData() {
        try {
            const response = await fetch('/api/location-data');
            if (response.ok) {
                bookingLocationData = await response.json();
                populateBookingCounties();
            } else {
                throw new Error('Failed to load location data');
            }
        } catch (error) {
            console.log('Using fallback location data for booking');
            loadBookingFallbackData();
        }
    }

    function populateBookingCounties() {
        const countySelect = document.getElementById('booking-county-select');
        const counties = Object.keys(bookingLocationData).sort();
        
        counties.forEach(county => {
            const option = document.createElement('option');
            option.value = county;
            option.textContent = county;
            countySelect.appendChild(option);
        });
    }

    function loadBookingFallbackData() {
        bookingLocationData = {
            "Bronx County": ["Bronx", "Fordham", "Riverdale"],
            "Kings County": ["Brooklyn", "Park Slope", "Williamsburg"],
            "New York County": ["Manhattan", "Upper East Side", "SoHo"],
            "Queens County": ["Queens", "Flushing", "Jamaica"],
            "Richmond County": ["Staten Island", "St. George", "Tottenville"]
        };
        populateBookingCounties();
    }

    // Handle county selection for booking
    document.getElementById('booking-county-select').addEventListener('change', function() {
        const citySelect = document.getElementById('booking-city-select');
        const selectedCounty = this.value;
        
        if (selectedCounty) {
            const cities = bookingLocationData[selectedCounty] || [];
            
            citySelect.innerHTML = '<option value="">Select City/Borough</option>';
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
            
            citySelect.disabled = false;
        } else {
            citySelect.innerHTML = '<option value="">Select County First</option>';
            citySelect.disabled = true;
        }
    });

    function fillDemoData() {
        const serviceTypes = ['personal_care', 'companionship', 'medical_assistance', 'housekeeping'];
        const dutyTypes = ['live_in', 'hourly', 'overnight', 'respite'];
        const counties = Object.keys(bookingLocationData);
        const genders = ['no_preference', 'female', 'male'];
        const languages = ['english', 'spanish', 'french', 'mandarin'];
        const bgChecks = ['standard', 'enhanced'];
        const paymentMethods = ['credit_card', 'debit_card', 'bank_transfer', 'insurance'];
        const mobilityLevels = ['independent', 'assisted', 'wheelchair', 'bedridden'];
        const urgencyLevels = ['scheduled', 'within_24h', 'today', 'asap'];
        const recurringSchedules = ['daily', 'weekly', 'bi_weekly', 'monthly'];
        const addresses = ['123 Main St', '456 Oak Ave', '789 Pine Rd', '321 Elm St', '654 Maple Dr'];
        const apartments = ['Apt 4B', 'Unit 12', 'Suite 305', '2nd Floor', 'Penthouse'];
        const instructions = [
            'Patient prefers morning care routine',
            'Needs assistance with medication reminders',
            'Enjoys classical music and reading',
            'Requires help with meal preparation',
            'Prefers quiet and calm environment'
        ];

        const rand = (arr) => arr[Math.floor(Math.random() * arr.length)];
        const randNum = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
        const futureDate = () => {
            const date = new Date();
            date.setDate(date.getDate() + randNum(1, 30));
            return date.toISOString().split('T')[0];
        };

        document.querySelector('[name="service_type"]').value = rand(serviceTypes);
        document.querySelector('[name="duty_type"]').value = rand(dutyTypes);
        document.querySelector('[name="service_date"]').value = futureDate();
        const startHour = randNum(6, 18);
        const endHour = startHour + randNum(2, 8);
        document.querySelector('[name="start_time"]').value = `${String(startHour).padStart(2, '0')}:00`;
        document.querySelector('[name="end_time"]').value = `${String(Math.min(endHour, 23)).padStart(2, '0')}:00`;
        
        // Randomly select service days
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const selectedDays = days.filter(() => Math.random() > 0.4);
        days.forEach(day => {
            document.querySelector(`[name="service_days[]"][value="${day}"]`).checked = selectedDays.includes(day);
        });
        document.querySelector('[name="duration_days"]').value = rand([1, 7, 15, 30]);
        
        // Set location
        const selectedCounty = rand(counties);
        document.getElementById('booking-county-select').value = selectedCounty;
        document.getElementById('booking-county-select').dispatchEvent(new Event('change'));
        setTimeout(() => {
            const cities = bookingLocationData[selectedCounty] || [];
            if (cities.length > 0) {
                document.getElementById('booking-city-select').value = rand(cities);
            }
        }, 100);
        
        document.querySelector('[name="street_address"]').value = rand(addresses);
        document.querySelector('[name="apartment_unit"]').value = Math.random() > 0.3 ? rand(apartments) : '';
        document.querySelector('[name="gender_preference"]').value = rand(genders);
        document.querySelector('[name="language_preference"]').value = rand(languages);
        document.querySelector('[name="background_check_level"]').value = rand(bgChecks);
        document.querySelector('[name="hourly_rate"]').value = randNum(15, 50);
        document.querySelector('[name="total_budget"]').value = randNum(500, 5000);
        document.querySelector('[name="payment_method"]').value = rand(paymentMethods);
        document.querySelector('[name="client_age"]').value = randNum(25, 95);
        document.querySelector('[name="mobility_level"]').value = rand(mobilityLevels);
        document.querySelector('[name="urgency_level"]').value = rand(urgencyLevels);
        document.querySelector('[name="transportation_needed"]').checked = Math.random() > 0.5;
        document.querySelector('[name="recurring_service"]').checked = Math.random() > 0.5;
        document.querySelector('[name="recurring_schedule"]').value = Math.random() > 0.5 ? rand(recurringSchedules) : '';
        document.querySelector('[name="special_instructions"]').value = rand(instructions);
    }
    </script>
</body>
</html>