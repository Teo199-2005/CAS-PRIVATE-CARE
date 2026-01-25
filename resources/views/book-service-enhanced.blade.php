<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Service - CAS Private Care</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
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
        
        /* Terms Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-container {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            padding: 30px;
            border-bottom: 3px solid #007bff;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px 12px 0 0;
        }
        
        .modal-logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .modal-header h2 {
            color: #007bff;
            margin: 10px 0;
            font-size: 28px;
            font-weight: 700;
        }
        
        .modal-subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 40px;
            position: relative;
            background: white;
        }
        
        .contract-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(0, 123, 255, 0.05);
            pointer-events: none;
            white-space: nowrap;
            z-index: 0;
            user-select: none;
        }
        
        .contract-content {
            position: relative;
            z-index: 1;
            line-height: 1.8;
            color: #333;
        }
        
        .contract-content h1 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .contract-content h2 {
            font-size: 18px;
            color: #0056b3;
            margin-top: 30px;
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        
        .contract-content p {
            margin-bottom: 15px;
            text-align: justify;
        }
        
        .contract-content ul {
            margin-left: 30px;
            margin-bottom: 15px;
        }
        
        .contract-content li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .highlight-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            margin: 20px 0;
        }
        
        .acknowledgment-box {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .acknowledgment-box h2 {
            background: none;
            border: none;
            padding: 0;
            margin: 0 0 15px 0;
        }
        
        .contract-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 30px;
            color: #666;
        }
        
        .contract-footer p {
            margin: 5px 0;
        }
        
        .modal-footer {
            padding: 25px 40px;
            border-top: 2px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }
        
        .scroll-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
            font-weight: 600;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .scroll-indicator svg {
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(5px); }
        }
        
        .scroll-indicator.hidden {
            display: none;
        }
        
        .checkbox-container {
            margin: 15px 0;
        }
        
        .checkbox-label {
            display: flex;
            align-items: start;
            gap: 12px;
            cursor: pointer;
            font-size: 15px;
            line-height: 1.5;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .checkbox-label:hover {
            background: #f8f9fa;
            border-color: #007bff;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin-top: 2px;
        }
        
        .checkbox-label input[type="checkbox"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .checkbox-label input[type="checkbox"]:checked + span {
            color: #007bff;
            font-weight: 600;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-primary:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        
        /* Custom Scrollbar for Modal */
        .modal-body::-webkit-scrollbar {
            width: 12px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #007bff;
            border-radius: 10px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #0056b3;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-card {
                padding: 20px 15px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .section-title {
                font-size: 16px;
            }
            
            .modal-container {
                max-width: 100%;
                max-height: 95vh;
                margin: 10px;
                border-radius: 8px;
            }
            
            .modal-header {
                padding: 20px;
            }
            
            .modal-header h2 {
                font-size: 22px;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .modal-footer {
                flex-direction: column;
                gap: 10px;
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary, .btn-demo {
                width: 100%;
                padding: 14px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .form-card {
                padding: 15px 10px;
            }
            
            .section-title {
                font-size: 15px;
            }
            
            input, select, textarea {
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .modal-header h2 {
                font-size: 18px;
            }
            
            .modal-logo {
                max-width: 150px;
            }
        }
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
                    <button type="button" class="btn-primary" onclick="showTermsModal()">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div id="termsModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <img src="/images/logo.png" alt="CAS Private Care" class="modal-logo" onerror="this.style.display='none'">
                <h2>Client Service Agreement</h2>
                <p class="modal-subtitle">Please read and accept the terms before proceeding</p>
            </div>
            
            <div class="modal-body" id="contractBody">
                <div class="contract-watermark">CAS Private Care</div>
                
                <div class="contract-content">
                    <h1 style="text-align: center; margin-bottom: 10px;">CLIENT SERVICE, PAYMENT & NON-CIRCUMVENTION AGREEMENT</h1>
                    <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">CAS Private Care LLC</p>
                    <p style="text-align: center; font-style: italic; color: #666; margin-bottom: 30px;">Effective Date: Automatically recorded upon electronic acceptance</p>
                    
                    <h2>1. PARTIES & PURPOSE</h2>
                    <p>This Client Service, Payment & Non-Circumvention Agreement ("Agreement") is entered into between <strong>CAS Private Care LLC</strong>, a New York-based service coordination and referral platform ("CAS Private Care," "Company," "we," "us," or "our"), and the individual or entity accepting this Agreement ("Client," "you," or "your").</p>
                    <p>The purpose of this Agreement is to define the terms under which CAS Private Care facilitates the referral, coordination, and payment processing of independent caregivers and household service providers.</p>
                    
                    <h2>2. NATURE OF SERVICES (IMPORTANT DISCLOSURE)</h2>
                    <p><strong>CAS Private Care LLC is NOT:</strong></p>
                    <ul>
                        <li>a home health agency</li>
                        <li>a medical provider</li>
                        <li>an employer of caregivers</li>
                    </ul>
                    <p><strong>CAS Private Care provides:</strong></p>
                    <ul>
                        <li>referral and matching services</li>
                        <li>scheduling coordination</li>
                        <li>technology and platform access</li>
                        <li>payment facilitation and administrative support</li>
                    </ul>
                    <p><strong>CAS Private Care does NOT:</strong></p>
                    <ul>
                        <li>provide medical or nursing services</li>
                        <li>supervise, direct, or control caregivers</li>
                        <li>determine how services are performed</li>
                    </ul>
                    <p>All services facilitated are strictly non-medical, including companionship, light household assistance, errands, and personal support.</p>
                    
                    <h2>3. INDEPENDENT CONTRACTOR STATUS (1099 DISCLOSURE)</h2>
                    <p>Client expressly acknowledges and agrees that:</p>
                    <ul>
                        <li>All caregivers introduced through CAS Private Care are <strong>independent contractors (1099)</strong>.</li>
                        <li>Caregivers are <strong>NOT employees</strong> of CAS Private Care.</li>
                    </ul>
                    <p><strong>CAS Private Care does not:</strong></p>
                    <ul>
                        <li>pay wages</li>
                        <li>withhold taxes</li>
                        <li>provide benefits</li>
                        <li>provide workers' compensation or unemployment insurance</li>
                    </ul>
                    <p><strong>Caregivers may:</strong></p>
                    <ul>
                        <li>accept or decline assignments</li>
                        <li>work for other clients or platforms</li>
                        <li>determine the manner and means of performing services</li>
                    </ul>
                    <p>No employer-employee, joint employer, partnership, or agency relationship is created.</p>
                    
                    <h2>4. CLIENT RESPONSIBILITIES</h2>
                    <p>Client agrees to:</p>
                    <ul>
                        <li>supervise and manage the caregiver directly</li>
                        <li>ensure a safe working environment</li>
                        <li>confirm that requested services are non-medical</li>
                        <li>comply with all applicable laws and regulations</li>
                        <li>communicate expectations clearly and honestly</li>
                    </ul>
                    <p>Client is solely responsible for determining caregiver suitability.</p>
                    
                    <h2>5. PAYMENT STRUCTURE & PASS-THROUGH PAYMENTS</h2>
                    <p>All payments are processed through CAS Private Care's platform.</p>
                    <p><strong>Payments include:</strong></p>
                    <ul>
                        <li>Caregiver Service Fee (pass-through payment)</li>
                        <li>Platform / Coordination / Technology Fee retained by CAS Private Care</li>
                    </ul>
                    <p><strong>CAS Private Care:</strong></p>
                    <ul>
                        <li>collects payment from Client</li>
                        <li>deducts its platform fee</li>
                        <li>remits remaining funds to the caregiver</li>
                    </ul>
                    <p>Payments do not constitute wages and do not create an employment relationship with CAS Private Care.</p>
                    
                    <h2>6. NON-REFUNDABLE PAYMENTS & CANCELLATION POLICY</h2>
                    <ul>
                        <li>All payments are <strong>NON-REFUNDABLE</strong> once a caregiver booking is confirmed.</li>
                        <li>Cancellation notice requirements are displayed at booking</li>
                        <li>Late cancellations may be charged in full</li>
                        <li>No refunds for dissatisfaction without documented breach</li>
                        <li>No refunds for caregiver unavailability beyond reasonable control</li>
                    </ul>
                    <p>CAS Private Care may, at its discretion, issue credits in lieu of refunds.</p>
                    
                    <h2>7. NO GUARANTEE OF AVAILABILITY OR PERFORMANCE</h2>
                    <p>CAS Private Care makes no guarantees regarding:</p>
                    <ul>
                        <li>caregiver availability</li>
                        <li>continuity of service</li>
                        <li>performance outcomes</li>
                    </ul>
                    <p>Caregivers operate independently and results may vary.</p>
                    
                    <h2>8. BACKGROUND CHECK & DUE DILIGENCE DISCLOSURE</h2>
                    <p>CAS Private Care may conduct limited screening, which may include:</p>
                    <ul>
                        <li>identity verification</li>
                        <li>experience review</li>
                        <li>basic background checks where permitted</li>
                    </ul>
                    <p><strong>CAS Private Care does not guarantee caregiver behavior or performance.</strong></p>
                    <p>Client is encouraged to conduct interviews and additional checks if desired.</p>
                    
                    <h2>9. NON-CIRCUMVENTION & PLATFORM FEE PROTECTION</h2>
                    <p class="highlight-box"><strong>(CRITICAL CLAUSE)</strong></p>
                    <p>Client acknowledges that CAS Private Care's services provide value through introduction, referral, coordination, and platform access.</p>
                    <p>Client agrees that during the term of this Agreement and for <strong>twelve (12) months</strong> after the last introduction or engagement, Client shall NOT, directly or indirectly:</p>
                    <ul>
                        <li>hire</li>
                        <li>engage</li>
                        <li>pay</li>
                        <li>contract with</li>
                        <li>continue services with</li>
                    </ul>
                    <p>any caregiver introduced through CAS Private Care outside of the CAS Private Care platform, without payment of the applicable platform fees.</p>
                    <p>This obligation applies regardless of whether the caregiver remains active on the platform.</p>
                    
                    <h2>10. FEE AVOIDANCE & LIQUIDATED DAMAGES</h2>
                    <p>If Client violates the Non-Circumvention clause, Client agrees to pay liquidated damages, which both parties agree represent a reasonable estimate of damages and not a penalty, equal to the greater of:</p>
                    <ul>
                        <li>twelve (12) months of platform fees that would have been paid, or</li>
                        <li>a flat referral conversion fee of USD $5,000 per caregiver</li>
                    </ul>
                    <p>CAS Private Care may pursue collection of such fees in addition to any other remedies permitted by law.</p>
                    
                    <h2>11. LIMITATION OF LIABILITY</h2>
                    <p>To the maximum extent permitted by law, CAS Private Care shall NOT be liable for:</p>
                    <ul>
                        <li>acts or omissions of caregivers</li>
                        <li>negligence or misconduct</li>
                        <li>personal injury or death</li>
                        <li>property damage</li>
                        <li>emotional distress</li>
                        <li>loss of income or opportunity</li>
                    </ul>
                    <p>CAS Private Care's total liability, if any, shall not exceed the platform fees paid by Client in the thirty (30) days preceding the claim.</p>
                    
                    <h2>12. INDEMNIFICATION</h2>
                    <p>Client agrees to indemnify, defend, and hold harmless CAS Private Care LLC, its owners, officers, employees, and affiliates from any claims, damages, losses, or expenses arising from:</p>
                    <ul>
                        <li>caregiver services</li>
                        <li>Client-caregiver relationship</li>
                        <li>Client's use of the platform</li>
                        <li>violation of this Agreement</li>
                    </ul>
                    
                    <h2>13. NO EMPLOYMENT OR JOINT EMPLOYER RELATIONSHIP</h2>
                    <p>Nothing in this Agreement creates:</p>
                    <ul>
                        <li>an employer-employee relationship</li>
                        <li>joint employment</li>
                        <li>partnership</li>
                        <li>agency relationship</li>
                    </ul>
                    <p>CAS Private Care is not the employer of record for any caregiver.</p>
                    
                    <h2>14. FORCE MAJEURE</h2>
                    <p>CAS Private Care shall not be liable for failure or delay caused by events beyond reasonable control, including but not limited to:</p>
                    <ul>
                        <li>natural disasters</li>
                        <li>power or internet outages</li>
                        <li>government actions</li>
                        <li>emergencies</li>
                        <li>illness or accidents</li>
                    </ul>
                    
                    <h2>15. DISPUTE RESOLUTION & ARBITRATION</h2>
                    <p>Any dispute arising out of or relating to this Agreement shall be resolved by <strong>binding arbitration</strong>, not court litigation.</p>
                    <ul>
                        <li>No class or collective actions permitted</li>
                        <li>Arbitration venue: New York</li>
                        <li>Governing law: State of New York</li>
                    </ul>
                    
                    <h2>16. ELECTRONIC SIGNATURE & RECORDS</h2>
                    <p>Client agrees that:</p>
                    <ul>
                        <li>clicking "I AGREE" constitutes a legally binding electronic signature</li>
                        <li>acceptance may be logged with timestamp, IP address, and agreement version</li>
                        <li>electronic records are enforceable under applicable law</li>
                    </ul>
                    
                    <h2>17. PRIVACY & DATA USE</h2>
                    <p>Client consents to:</p>
                    <ul>
                        <li>collection and use of personal information</li>
                        <li>sharing necessary information with caregivers</li>
                        <li>payment processing via Stripe or similar providers</li>
                        <li>data handling per the Privacy Policy</li>
                    </ul>
                    
                    <h2>18. ENTIRE AGREEMENT & MODIFICATIONS</h2>
                    <p>This Agreement constitutes the entire agreement between Client and CAS Private Care LLC and supersedes all prior communications.</p>
                    <p>CAS Private Care may update this Agreement, with changes effective upon posting.</p>
                    
                    <div class="acknowledgment-box">
                        <h2 style="text-align: center;">ACKNOWLEDGMENT</h2>
                        <p>By clicking "I HAVE READ AND AGREE TO ALL TERMS ABOVE", Client confirms that they have read, understood, and voluntarily agreed to be legally bound by this Agreement.</p>
                    </div>
                    
                    <div class="contract-footer">
                        <p><strong>CAS Private Care LLC</strong></p>
                        <p>New York-Based Service Coordination Platform</p>
                        <p>Document Version: 1.0 | Effective: January 2026</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="scroll-indicator" id="scrollIndicator">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Please scroll to read the entire agreement</span>
                </div>
                
                <div class="checkbox-container">
                    <label class="checkbox-label">
                        <input type="checkbox" id="scrolledCheckbox" disabled>
                        <span>I have read and scrolled through the entire agreement</span>
                    </label>
                </div>
                
                <div class="checkbox-container">
                    <label class="checkbox-label">
                        <input type="checkbox" id="agreeCheckbox" disabled>
                        <span><strong>I agree to all terms and conditions above</strong></span>
                    </label>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn-secondary" onclick="closeTermsModal()">Cancel</button>
                    <button type="button" class="btn-primary" id="submitAgreementBtn" disabled onclick="submitBookingForm()">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Terms Modal Functionality
    let hasScrolledToBottom = false;
    
    function showTermsModal() {
        // Validate form first
        const form = document.querySelector('form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        document.getElementById('termsModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Reset checkboxes
        document.getElementById('scrolledCheckbox').checked = false;
        document.getElementById('agreeCheckbox').checked = false;
        document.getElementById('submitAgreementBtn').disabled = true;
        hasScrolledToBottom = false;
        
        // Show scroll indicator
        document.getElementById('scrollIndicator').classList.remove('hidden');
        
        // Add scroll event listener
        const modalBody = document.getElementById('contractBody');
        modalBody.scrollTop = 0; // Reset scroll position
        modalBody.addEventListener('scroll', checkScrollPosition);
    }
    
    function closeTermsModal() {
        document.getElementById('termsModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    function checkScrollPosition() {
        const modalBody = document.getElementById('contractBody');
        const scrollPosition = modalBody.scrollTop + modalBody.clientHeight;
        const scrollHeight = modalBody.scrollHeight;
        
        // Check if scrolled to bottom (with 50px tolerance)
        if (scrollPosition >= scrollHeight - 50 && !hasScrolledToBottom) {
            hasScrolledToBottom = true;
            document.getElementById('scrollIndicator').classList.add('hidden');
            document.getElementById('scrolledCheckbox').disabled = false;
            
            // Show success message
            const indicator = document.getElementById('scrollIndicator');
            indicator.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17L4 12" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span style="color: #28a745;">Thank you for reading the agreement!</span>';
            indicator.style.background = '#d4edda';
            indicator.style.borderColor = '#28a745';
            indicator.classList.remove('hidden');
            
            setTimeout(() => {
                indicator.classList.add('hidden');
            }, 3000);
        }
    }
    
    // Enable agree checkbox when scrolled checkbox is checked
    document.addEventListener('DOMContentLoaded', function() {
        const scrolledCheckbox = document.getElementById('scrolledCheckbox');
        const agreeCheckbox = document.getElementById('agreeCheckbox');
        const submitBtn = document.getElementById('submitAgreementBtn');
        
        scrolledCheckbox.addEventListener('change', function() {
            if (this.checked) {
                agreeCheckbox.disabled = false;
            } else {
                agreeCheckbox.disabled = true;
                agreeCheckbox.checked = false;
                submitBtn.disabled = true;
            }
        });
        
        agreeCheckbox.addEventListener('change', function() {
            submitBtn.disabled = !this.checked;
        });
    });
    
    function submitBookingForm() {
        // Log agreement acceptance
        const acceptanceData = {
            timestamp: new Date().toISOString(),
            ipAddress: 'client-side', // This would be captured server-side
            agreementVersion: '1.0',
            scrolledToBottom: hasScrolledToBottom,
            agreedToTerms: document.getElementById('agreeCheckbox').checked
        };
        
        console.log('Terms acceptance logged:', acceptanceData);
        
        // Submit the form
        document.querySelector('form').submit();
        
        // Close modal
        closeTermsModal();
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTermsModal();
        }
    });
    
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
    @include('partials.cookie-consent')
</body>
</html>