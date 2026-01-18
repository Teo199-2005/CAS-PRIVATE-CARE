<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TaxDocument;
use App\Models\TaxForm1099;
use App\Services\Form1099Service;

class TaxDocumentController extends Controller
{
    protected $form1099Service;
    
    public function __construct(Form1099Service $form1099Service)
    {
        $this->form1099Service = $form1099Service;
    }
    
    /**
     * Get W9 status for current user
     */
    public function getW9Status()
    {
        $user = Auth::user();
        
        // Get latest W9 document
        $w9Document = TaxDocument::where('user_id', $user->id)
            ->where('document_type', 'w9')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return response()->json([
            'success' => true,
            'w9_submitted' => $user->w9_submitted ?? false,
            'w9_verified' => $user->w9_verified ?? false,
            'w9_submitted_at' => $user->w9_submitted_at,
            'has_tax_id' => !empty($user->ssn_encrypted) || !empty($user->itin_encrypted),
            'tax_classification' => $user->tax_classification,
            'legal_name' => $user->legal_name,
            'document' => $w9Document ? [
                'id' => $w9Document->id,
                'status' => $w9Document->status,
                'submitted_at' => $w9Document->submitted_at,
                'verified_at' => $w9Document->verified_at,
                'rejection_reason' => $w9Document->rejection_reason
            ] : null
        ]);
    }
    
    /**
     * Submit W9 form data
     */
    public function submitW9(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'legal_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'tax_classification' => 'required|in:individual,sole_proprietor,single_member_llc,c_corporation,s_corporation,partnership,trust_estate,other',
            'tax_id_type' => 'required|in:ssn,itin,ein',
            'tax_id' => 'required|string', // Will be encrypted
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:10',
            'signature' => 'required|string', // Base64 signature image or typed name
            'certification_date' => 'required|date'
        ]);
        
        try {
            // Encrypt the tax ID
            $encryptedTaxId = encrypt($validated['tax_id']);
            $taxIdLastFour = substr($validated['tax_id'], -4);
            
            // Update user record
            $updateData = [
                'legal_name' => $validated['legal_name'],
                'business_name' => $validated['business_name'],
                'tax_classification' => $validated['tax_classification'],
                'tax_id_type' => $validated['tax_id_type'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'w9_submitted' => true,
                'w9_submitted_at' => now()
            ];
            
            // Store encrypted tax ID based on type
            if ($validated['tax_id_type'] === 'ssn') {
                $updateData['ssn_encrypted'] = $encryptedTaxId;
            } elseif ($validated['tax_id_type'] === 'itin') {
                $updateData['itin_encrypted'] = $encryptedTaxId;
            }
            
            $user->update($updateData);
            
            // Create tax document record
            $taxDocument = TaxDocument::create([
                'user_id' => $user->id,
                'document_type' => 'w9',
                'form_data' => [
                    'legal_name' => $validated['legal_name'],
                    'business_name' => $validated['business_name'],
                    'tax_classification' => $validated['tax_classification'],
                    'tax_id_type' => $validated['tax_id_type'],
                    'tax_id_last_four' => $taxIdLastFour,
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'zip_code' => $validated['zip_code'],
                    'signature_type' => 'electronic',
                    'certification_date' => $validated['certification_date']
                ],
                'status' => 'submitted',
                'submitted_at' => now()
            ]);
            
            Log::info("W9 submitted", [
                'user_id' => $user->id,
                'document_id' => $taxDocument->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'W9 form submitted successfully',
                'document_id' => $taxDocument->id,
                'status' => 'pending_review'
            ]);
            
        } catch (\Exception $e) {
            Log::error("W9 submission failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit W9 form: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload W9 PDF document
     */
    public function uploadW9(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'w9_file' => 'required|file|mimes:pdf|max:5120' // 5MB max
        ]);
        
        try {
            $file = $request->file('w9_file');
            $filename = "w9_{$user->id}_" . time() . ".pdf";
            $path = $file->storeAs('tax-documents/w9', $filename, 'private');
            
            // Create or update tax document
            $taxDocument = TaxDocument::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'document_type' => 'w9',
                    'status' => 'submitted'
                ],
                [
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'submitted',
                    'submitted_at' => now()
                ]
            );
            
            // Update user
            $user->update([
                'w9_submitted' => true,
                'w9_submitted_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'W9 document uploaded successfully',
                'document_id' => $taxDocument->id
            ]);
            
        } catch (\Exception $e) {
            Log::error("W9 upload failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload W9 document'
            ], 500);
        }
    }
    
    /**
     * Get 1099 forms for current user
     */
    public function get1099Forms()
    {
        $user = Auth::user();
        
        $forms = TaxForm1099::where('user_id', $user->id)
            ->orderBy('tax_year', 'desc')
            ->get()
            ->map(function ($form) {
                return [
                    'id' => $form->id,
                    'tax_year' => $form->tax_year,
                    'form_type' => $form->form_type,
                    'total_compensation' => $form->total_compensation,
                    'status' => $form->status,
                    'generated_at' => $form->generated_at,
                    'sent_to_recipient_at' => $form->sent_to_recipient_at,
                    'has_pdf' => !empty($form->pdf_path_copy_b)
                ];
            });
        
        return response()->json([
            'success' => true,
            'forms' => $forms
        ]);
    }
    
    /**
     * Download 1099 PDF
     */
    public function download1099($formId)
    {
        $user = Auth::user();
        
        $form = TaxForm1099::where('id', $formId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        if (empty($form->pdf_path_copy_b) || !Storage::exists($form->pdf_path_copy_b)) {
            return response()->json([
                'success' => false,
                'message' => '1099 PDF not available'
            ], 404);
        }
        
        return Storage::download(
            $form->pdf_path_copy_b,
            "1099-NEC_{$form->tax_year}_{$user->name}.pdf"
        );
    }
    
    /**
     * ADMIN: Get pending W9 submissions
     */
    public function adminGetPendingW9s()
    {
        $pendingW9s = TaxDocument::with('user')
            ->where('document_type', 'w9')
            ->whereIn('status', ['submitted', 'pending_review'])
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'user_id' => $doc->user_id,
                    'user_name' => $doc->user?->name,
                    'user_email' => $doc->user?->email,
                    'user_type' => $doc->user?->user_type,
                    'submitted_at' => $doc->submitted_at,
                    'has_file' => !empty($doc->file_path),
                    'has_form_data' => !empty($doc->form_data),
                    'form_data' => $doc->form_data
                ];
            });
        
        return response()->json([
            'success' => true,
            'pending_count' => $pendingW9s->count(),
            'submissions' => $pendingW9s
        ]);
    }
    
    /**
     * ADMIN: Verify W9 submission
     */
    public function adminVerifyW9(Request $request, $documentId)
    {
        $admin = Auth::user();
        
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $document = TaxDocument::findOrFail($documentId);
        
        $document->markAsVerified($admin->id);
        
        // Update user's W9 status
        $document->user->update([
            'w9_verified' => true,
            'w9_verified_at' => now(),
            'w9_verified_by' => $admin->id
        ]);
        
        Log::info("W9 verified", [
            'document_id' => $documentId,
            'user_id' => $document->user_id,
            'verified_by' => $admin->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'W9 verified successfully'
        ]);
    }
    
    /**
     * ADMIN: Reject W9 submission
     */
    public function adminRejectW9(Request $request, $documentId)
    {
        $admin = Auth::user();
        
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        $document = TaxDocument::findOrFail($documentId);
        $document->markAsRejected($request->reason, $admin->id);
        
        return response()->json([
            'success' => true,
            'message' => 'W9 rejected'
        ]);
    }
    
    /**
     * ADMIN: Generate 1099s for a tax year
     */
    public function adminGenerate1099s(Request $request)
    {
        $admin = Auth::user();
        
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'tax_year' => 'required|integer|min:2020|max:' . date('Y')
        ]);
        
        $result = $this->form1099Service->batchGenerate1099s($request->tax_year);
        
        return response()->json([
            'success' => true,
            'result' => $result
        ]);
    }
    
    /**
     * ADMIN: Get 1099 summary for a tax year
     */
    public function adminGet1099Summary($taxYear)
    {
        $summary = $this->form1099Service->get1099Summary($taxYear);
        $forms = $this->form1099Service->getAll1099sForYear($taxYear);
        
        return response()->json([
            'success' => true,
            'summary' => $summary,
            'forms' => $forms->map(function ($form) {
                return [
                    'id' => $form->id,
                    'user_id' => $form->user_id,
                    'user_name' => $form->user?->name,
                    'user_email' => $form->user?->email,
                    'total_compensation' => $form->total_compensation,
                    'status' => $form->status,
                    'sent_to_recipient_at' => $form->sent_to_recipient_at
                ];
            })
        ]);
    }
    
    /**
     * ADMIN: Send 1099 to recipient
     */
    public function adminSend1099(Request $request, $formId)
    {
        $admin = Auth::user();
        
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'method' => 'required|in:email,portal'
        ]);
        
        $form = TaxForm1099::with('user')->findOrFail($formId);
        
        $this->form1099Service->markAsSentToRecipient(
            $formId,
            $request->method,
            $form->user->email
        );
        
        // TODO: Send actual email notification
        
        return response()->json([
            'success' => true,
            'message' => '1099 marked as sent to recipient'
        ]);
    }
}
