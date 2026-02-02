<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CAS Private Care</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .profile-header { background: white; border-radius: 10px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; background: #007bff; color: white; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 15px; }
        .form-card { background: white; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        .form-group.full-width { flex: 100%; }
        label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .btn-primary { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-secondary { background: #6c757d; color: white; padding: 8px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-primary:hover { background: #0056b3; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; }
        .checkbox-group input[type="checkbox"] { width: auto; }
        .notification-prefs { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
        .status-badge { background: #28a745; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; }
        .readonly-field { background-color: #f8f9fa; color: #6c757d; cursor: not-allowed; }
        .help-text { color: #6c757d; font-size: 12px; margin-top: 5px; }
        select:disabled { background-color: #e9ecef; color: #6c757d; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">MS</div>
            <h2>Maria Santos</h2>
            <p><span class="status-badge">Premium Client</span></p>
            <p>Member since Jan 2024</p>
        </div>

        <form action="/profile/update" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Personal Information -->
            <div class="form-card">
                <div class="section-title">Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="Demo" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="Caregiver" required pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="caregiver@demo.com" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" placeholder="(646) 282-8282" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Address</label>
                        <input type="text" name="address" placeholder="Street Address" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="New York" readonly class="readonly-field">
                    </div>
                    <div class="form-group">
                        <label>ZIP Code</label>
                        <input type="text" name="zip_code" pattern="[0-9]{5}" placeholder="12345" maxlength="5" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>County</label>
                        <select name="county" id="county-select" required onchange="updateCities(this.value, 'city-select')">
                            <option value="">Select County</option>
                            <option value="Albany County">Albany County</option>
                            <option value="Bronx County">Bronx County</option>
                            <option value="Kings County">Kings County</option>
                            <option value="Nassau County">Nassau County</option>
                            <option value="New York County" selected>New York County</option>
                            <option value="Queens County">Queens County</option>
                            <option value="Richmond County">Richmond County</option>
                            <option value="Suffolk County">Suffolk County</option>
                            <option value="Westchester County">Westchester County</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <select name="city" id="city-select" required>
                            <option value="">Select City</option>
                            <option value="Manhattan">Manhattan</option>
                            <option value="Upper East Side">Upper East Side</option>
                            <option value="Upper West Side">Upper West Side</option>
                            <option value="Greenwich Village">Greenwich Village</option>
                            <option value="SoHo">SoHo</option>
                            <option value="Tribeca">Tribeca</option>
                            <option value="Chelsea">Chelsea</option>
                            <option value="Midtown">Midtown</option>
                            <option value="Hell's Kitchen">Hell's Kitchen</option>
                            <option value="Times Square">Times Square</option>
                            <option value="Financial District">Financial District</option>
                            <option value="Lower East Side">Lower East Side</option>
                            <option value="East Village">East Village</option>
                            <option value="Chinatown">Chinatown</option>
                            <option value="Little Italy">Little Italy</option>
                            <option value="Harlem">Harlem</option>
                            <option value="Washington Heights">Washington Heights</option>
                            <option value="Inwood" selected>Inwood</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="form-card">
                <div class="section-title">Emergency Contact</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Name</label>
                        <input type="text" name="emergency_contact_name" value="Jane Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="tel" name="emergency_contact_phone" value="(646) 282-8282" required>
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="emergency_contact_relationship" required>
                            <option value="daughter" selected>Daughter</option>
                            <option value="son">Son</option>
                            <option value="spouse">Spouse</option>
                            <option value="parent">Parent</option>
                            <option value="sibling">Sibling</option>
                            <option value="friend">Friend</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="form-card">
                <div class="section-title">Medical Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mobility Level</label>
                        <select name="mobility_level">
                            <option value="independent" selected>Independent</option>
                            <option value="assisted">Assisted</option>
                            <option value="wheelchair">Wheelchair</option>
                            <option value="bedridden">Bedridden</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Medical Conditions</label>
                        <input type="text" name="medical_conditions" placeholder="Diabetes, Hypertension (comma separated)">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Allergies</label>
                        <input type="text" name="allergies" placeholder="Penicillin, Nuts (comma separated)">
                    </div>
                    <div class="form-group">
                        <label>Current Medications</label>
                        <input type="text" name="medications" placeholder="Metformin, Lisinopril (comma separated)">
                    </div>
                </div>
            </div>

            <!-- Insurance Information -->
            <div class="form-card">
                <div class="section-title">Insurance Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Insurance Provider</label>
                        <input type="text" name="insurance_provider" placeholder="e.g., Blue Cross Blue Shield">
                    </div>
                    <div class="form-group">
                        <label>Policy Number</label>
                        <input type="text" name="insurance_policy_number" placeholder="Policy number">
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div class="form-card">
                <div class="section-title">Account Settings</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Account Type</label>
                        <select name="account_type">
                            <option value="basic">Basic</option>
                            <option value="premium" selected>Premium</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="two_factor_enabled" id="2fa">
                            <label for="2fa">Enable Two-Factor Authentication</label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <label>Notification Preferences</label>
                    <div class="notification-prefs">
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications[]" value="email_bookings" id="email_bookings" checked>
                            <label for="email_bookings">Email - Bookings</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications[]" value="sms_reminders" id="sms_reminders" checked>
                            <label for="sms_reminders">SMS - Reminders</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications[]" value="push_updates" id="push_updates">
                            <label for="push_updates">Push - Updates</label>
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" name="notifications[]" value="email_promotions" id="email_promotions">
                            <label for="email_promotions">Email - Promotions</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <div class="form-card">
                <div class="section-title">Personal Bio</div>
                <div class="form-group full-width">
                    <textarea name="bio" placeholder="Tell us about yourself, your care needs, preferences, or any other information that would help caregivers provide better service..."></textarea>
                </div>
            </div>

            <!-- Change Password -->
            <div class="form-card">
                <div class="section-title">Change Password</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="8">
                        <small>8 minimum characters</small>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password">
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn-primary">Update Profile</button>
                <button type="button" class="btn-secondary" style="margin-left: 15px;">Cancel</button>
            </div>
        </form>
    </div>
</body>
<script>
    // County-City mapping
    const countyData = {
        "New York County": ["Manhattan", "Upper East Side", "Upper West Side", "Greenwich Village", "SoHo", "Tribeca", "Chelsea", "Midtown", "Hell's Kitchen", "Times Square", "Financial District", "Lower East Side", "East Village", "Chinatown", "Little Italy", "Harlem", "Washington Heights", "Inwood"],
        "Kings County": ["Brooklyn", "Park Slope", "Williamsburg", "DUMBO", "Bay Ridge", "Bensonhurst", "Crown Heights", "Bushwick"],
        "Queens County": ["Queens", "Flushing", "Jamaica", "Astoria", "Long Island City", "Forest Hills", "Elmhurst"],
        "Bronx County": ["Bronx", "Fordham", "Riverdale", "Throggs Neck", "Pelham Bay"],
        "Richmond County": ["Staten Island", "St. George", "Tottenville", "New Brighton"],
        "Nassau County": ["Hempstead", "Long Beach", "Glen Cove", "Freeport", "Valley Stream", "Inwood"],
        "Suffolk County": ["Huntington", "Brookhaven", "Islip", "Babylon", "Smithtown"],
        "Westchester County": ["White Plains", "Yonkers", "New Rochelle", "Mount Vernon", "Scarsdale"]
    };

    function updateCities(county, citySelectId) {
        const citySelect = document.getElementById(citySelectId);
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (county && countyData[county]) {
            countyData[county].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                if (city === 'Inwood' && county === 'New York County') {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCities('New York County', 'city-select');
    });

    // ZIP code validation
    document.querySelector('input[name="zip_code"]').addEventListener('input', function() {
        const zipCode = this.value;
        if (zipCode.length === 5 && /^\d{5}$/.test(zipCode)) {
            // Basic NY ZIP code ranges validation
            const zipNum = parseInt(zipCode);
            if ((zipNum >= 10001 && zipNum <= 14999) || // NYC area
                (zipNum >= 6390 && zipNum <= 6390) ||   // Fishers Island
                (zipNum >= 10901 && zipNum <= 10998) || // Westchester
                (zipNum >= 11001 && zipNum <= 11999) || // Long Island
                (zipNum >= 12001 && zipNum <= 12999) || // Albany area
                (zipNum >= 13001 && zipNum <= 13999) || // Syracuse area
                (zipNum >= 14001 && zipNum <= 14999)) { // Rochester/Buffalo area
                this.style.borderColor = '#28a745';
            } else {
                this.style.borderColor = '#dc3545';
            }
        } else {
            this.style.borderColor = '#ddd';
        }
    });

    // Auto-calculate age from birthdate
    document.querySelector('input[name="birthdate"]').addEventListener('change', function() {
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        document.querySelector('input[name="age"]').value = age;
    });
    

</script>
</html>