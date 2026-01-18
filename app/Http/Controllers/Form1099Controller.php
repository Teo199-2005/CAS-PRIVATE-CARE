<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TaxForm1099;
use App\Services\Form1099Service;

class Form1099Controller extends Controller
{
    protected $form1099Service;
    
    public function __construct(Form1099Service $form1099Service)
    {
        $this->form1099Service = $form1099Service;
    }
    
    /**
     * Get 1099 forms for current user
     */
    public function getMyForms()
    {
        $user = Auth::user();
        
        $forms = TaxForm1099::where('user_id', $user->id)
            ->orderBy('tax_year', 'desc')
            ->get()
            ->map(function ($form) {
                return [
                    'id' => $form->id,
                    'tax_year' => $form->tax_year,
                    'status' => $form->status,
                    'status_label' => $this->getStatusLabel($form->status),
                    'total_compensation' => $form->total_compensation,
                    'generated_at' => $form->generated_at,
                    'sent_at' => $form->sent_at,
                    'can_download' => $form->status === 'finalized' || $form->status === 'sent'
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
    public function download($formId)
    {
        $user = Auth::user();
        
        $form = TaxForm1099::where('id', $formId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        if (!in_array($form->status, ['finalized', 'sent'])) {
            return response()->json(['error' => '1099 form is not yet available for download'], 400);
        }
        
        // Generate PDF content
        $pdfContent = $this->generatePDFContent($form);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="1099-NEC-' . $form->tax_year . '.pdf"');
    }
    
    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'draft' => 'Draft',
            'pending_review' => 'Pending Review',
            'finalized' => 'Finalized',
            'sent' => 'Sent',
            'corrected' => 'Corrected'
        ];
        
        return $labels[$status] ?? $status;
    }
    
    protected function generatePDFContent(TaxForm1099 $form)
    {
        // Simple text-based PDF representation
        // In production, use a proper PDF library like TCPDF or DomPDF
        
        $user = $form->user;
        $content = "IRS Form 1099-NEC\n";
        $content .= "Tax Year: {$form->tax_year}\n\n";
        $content .= "PAYER'S Information:\n";
        $content .= "CAS - Caregiver Assignment Service\n";
        $content .= "EIN: XX-XXXXXXX\n\n";
        $content .= "RECIPIENT'S Information:\n";
        $content .= "Name: {$user->name}\n";
        $content .= "TIN: XXX-XX-" . substr($form->tin ?? '0000', -4) . "\n";
        $content .= "Address: {$form->recipient_address}\n\n";
        $content .= "Box 1 - Nonemployee Compensation: $" . number_format($form->total_compensation, 2) . "\n";
        
        if ($form->federal_income_tax_withheld > 0) {
            $content .= "Box 4 - Federal Income Tax Withheld: $" . number_format($form->federal_income_tax_withheld, 2) . "\n";
        }
        
        if ($form->state_income > 0) {
            $content .= "Box 5 - State Tax Withheld: $" . number_format($form->state_tax_withheld ?? 0, 2) . "\n";
            $content .= "Box 6 - State/Payer's State No.: {$form->state_payer_number}\n";
            $content .= "Box 7 - State Income: $" . number_format($form->state_income, 2) . "\n";
        }
        
        // Return as PDF (simplified - use proper PDF library in production)
        return $content;
    }
    
    // ==========================================
    // ADMIN ENDPOINTS
    // ==========================================
    
    /**
     * ADMIN: Get 1099 forms summary
     */
    public function adminGetSummary(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $year = $request->input('year', date('Y') - 1);
        
        $forms = TaxForm1099::where('tax_year', $year)
            ->with('user')
            ->get();
        
        $summary = [
            'total_forms' => $forms->count(),
            'total_compensation' => $forms->sum('total_compensation'),
            'by_status' => [
                'draft' => $forms->where('status', 'draft')->count(),
                'pending_review' => $forms->where('status', 'pending_review')->count(),
                'finalized' => $forms->where('status', 'finalized')->count(),
                'sent' => $forms->where('status', 'sent')->count(),
                'corrected' => $forms->where('status', 'corrected')->count()
            ],
            'threshold_amount' => 600
        ];
        
        return response()->json([
            'success' => true,
            'year' => $year,
            'summary' => $summary
        ]);
    }
    
    /**
     * ADMIN: Get all 1099 forms
     */
    public function adminGetForms(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $year = $request->input('year', date('Y') - 1);
        
        $query = TaxForm1099::with('user')
            ->where('tax_year', $year)
            ->orderBy('total_compensation', 'desc');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $forms = $query->paginate(50);
        
        return response()->json([
            'success' => true,
            'year' => $year,
            'forms' => $forms
        ]);
    }
    
    /**
     * ADMIN: Generate 1099 forms for tax year
     */
    public function adminGenerateForms(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') - 1)
        ]);
        
        $result = $this->form1099Service->generateAnnual1099s($validated['year']);
        
        Log::info("1099 forms generated", [
            'admin_id' => $admin->id,
            'year' => $validated['year'],
            'result' => $result
        ]);
        
        return response()->json($result);
    }
    
    /**
     * ADMIN: Get 1099 preview for a contractor
     */
    public function adminPreviewForm($userId, Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $year = $request->input('year', date('Y') - 1);
        
        $preview = $this->form1099Service->previewForm($userId, $year);
        
        return response()->json($preview);
    }
    
    /**
     * ADMIN: Finalize 1099 form
     */
    public function adminFinalizeForm($formId)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $form = TaxForm1099::findOrFail($formId);
        
        if (!in_array($form->status, ['draft', 'pending_review'])) {
            return response()->json(['error' => 'Form cannot be finalized from current status'], 400);
        }
        
        $form->update([
            'status' => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $admin->id
        ]);
        
        Log::info("1099 form finalized", [
            'form_id' => $form->id,
            'user_id' => $form->user_id,
            'admin_id' => $admin->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '1099 form has been finalized'
        ]);
    }
    
    /**
     * ADMIN: Send 1099 to contractor
     */
    public function adminSendForm($formId)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $form = TaxForm1099::with('user')->findOrFail($formId);
        
        if ($form->status !== 'finalized') {
            return response()->json(['error' => 'Form must be finalized before sending'], 400);
        }
        
        // Send email notification (simplified)
        try {
            // In production: Use proper email service
            // Mail::to($form->user->email)->send(new Form1099Notification($form));
            
            $form->update([
                'status' => 'sent',
                'sent_at' => now()
            ]);
            
            Log::info("1099 form sent to contractor", [
                'form_id' => $form->id,
                'user_id' => $form->user_id,
                'email' => $form->user->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '1099 form sent to ' . $form->user->email
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send 1099 form", [
                'form_id' => $form->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to send email'], 500);
        }
    }
    
    /**
     * ADMIN: Bulk finalize forms
     */
    public function adminBulkFinalize(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'form_ids' => 'required|array',
            'form_ids.*' => 'integer|exists:tax_forms_1099,id'
        ]);
        
        $finalized = 0;
        $skipped = 0;
        
        foreach ($validated['form_ids'] as $formId) {
            $form = TaxForm1099::find($formId);
            if ($form && in_array($form->status, ['draft', 'pending_review'])) {
                $form->update([
                    'status' => 'finalized',
                    'finalized_at' => now(),
                    'finalized_by' => $admin->id
                ]);
                $finalized++;
            } else {
                $skipped++;
            }
        }
        
        Log::info("Bulk 1099 finalization", [
            'admin_id' => $admin->id,
            'finalized' => $finalized,
            'skipped' => $skipped
        ]);
        
        return response()->json([
            'success' => true,
            'finalized' => $finalized,
            'skipped' => $skipped
        ]);
    }
    
    /**
     * ADMIN: Bulk send forms
     */
    public function adminBulkSend(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'form_ids' => 'required|array',
            'form_ids.*' => 'integer|exists:tax_forms_1099,id'
        ]);
        
        $sent = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($validated['form_ids'] as $formId) {
            $form = TaxForm1099::with('user')->find($formId);
            if ($form && $form->status === 'finalized') {
                try {
                    $form->update([
                        'status' => 'sent',
                        'sent_at' => now()
                    ]);
                    $sent++;
                } catch (\Exception $e) {
                    $errors[] = ['form_id' => $formId, 'error' => $e->getMessage()];
                }
            } else {
                $skipped++;
            }
        }
        
        Log::info("Bulk 1099 send", [
            'admin_id' => $admin->id,
            'sent' => $sent,
            'skipped' => $skipped,
            'errors' => count($errors)
        ]);
        
        return response()->json([
            'success' => true,
            'sent' => $sent,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }
    
    /**
     * ADMIN: Download 1099 form for any contractor
     */
    public function adminDownloadForm($formId)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $form = TaxForm1099::with('user')->findOrFail($formId);
        
        $pdfContent = $this->generatePDFContent($form);
        
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="1099-NEC-' . $form->tax_year . '-' . $form->user->id . '.pdf"');
    }
    
    /**
     * ADMIN: Create correction for 1099
     */
    public function adminCreateCorrection($formId, Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'total_compensation' => 'required|numeric|min:0',
            'reason' => 'required|string'
        ]);
        
        $originalForm = TaxForm1099::findOrFail($formId);
        
        if (!in_array($originalForm->status, ['finalized', 'sent'])) {
            return response()->json(['error' => 'Only finalized or sent forms can be corrected'], 400);
        }
        
        // Create corrected form
        $correctedForm = $originalForm->replicate();
        $correctedForm->total_compensation = $validated['total_compensation'];
        $correctedForm->status = 'corrected';
        $correctedForm->is_corrected = true;
        $correctedForm->original_form_id = $originalForm->id;
        $correctedForm->correction_reason = $validated['reason'];
        $correctedForm->generated_at = now();
        $correctedForm->finalized_at = null;
        $correctedForm->sent_at = null;
        $correctedForm->save();
        
        Log::info("1099 correction created", [
            'original_form_id' => $originalForm->id,
            'corrected_form_id' => $correctedForm->id,
            'admin_id' => $admin->id,
            'reason' => $validated['reason']
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Corrected 1099 form created',
            'corrected_form_id' => $correctedForm->id
        ]);
    }
    
    /**
     * ADMIN: Get contractors requiring 1099
     */
    public function adminGetContractorsRequiring1099(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $year = $request->input('year', date('Y') - 1);
        
        $contractors = $this->form1099Service->getContractorsRequiring1099($year);
        
        return response()->json([
            'success' => true,
            'year' => $year,
            'threshold' => 600,
            'contractors' => $contractors
        ]);
    }
}
