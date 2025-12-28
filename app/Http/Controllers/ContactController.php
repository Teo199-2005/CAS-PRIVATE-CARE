<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'service_type' => 'required|string|in:Home Care,Private Duty Nursing,Care Management,Bedside Care',
            'location' => 'required|string|in:New York City (Inclusive of five boroughs),Long Island,Westchester,New Jersey,Connecticut',
            'additional_info' => 'nullable|string|max:2000',
            'newsletter' => 'nullable|boolean'
        ]);

        try {
            // Format phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $validated['phone']);
            if (strlen($phoneNumber) === 10) {
                $formattedPhone = '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6, 4);
            } else {
                $formattedPhone = $validated['phone'];
            }

            // Prepare email data
            $emailData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $formattedPhone,
                'email' => $validated['email'],
                'service_type' => $validated['service_type'],
                'location' => $validated['location'],
                'additional_info' => $validated['additional_info'] ?? 'N/A',
                'newsletter' => $request->has('newsletter') && $request->newsletter == '1' ? 'Yes' : 'No',
                'submitted_at' => now()->format('F j, Y \a\t g:i A')
            ];

            // Send email (you can configure this later with your email service)
            // For now, we'll log it and show success message
            Log::info('Contact form submission', $emailData);

            // TODO: Uncomment and configure when email is set up
            /*
            Mail::send('emails.contact', $emailData, function ($message) use ($validated) {
                $message->to('contact@casprivatecare.online')
                        ->subject('New Contact Form Submission - ' . $validated['service_type']);
            });
            */

            return redirect()->route('contact')->with('success', 'Thank you for contacting us! We\'ll get back to you soon.');

        } catch (\Exception $e) {
            Log::error('Contact form submission error', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->route('contact')
                ->withInput()
                ->withErrors(['error' => 'Sorry, there was an error submitting your message. Please try again or contact us directly.']);
        }
    }
}


