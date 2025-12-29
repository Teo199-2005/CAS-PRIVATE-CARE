<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    /**
     * Get all payment methods for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($method) {
                if ($method->type === 'card') {
                    return [
                        'id' => $method->id,
                        'type' => $method->card_type,
                        'last4' => $method->last_four,
                        'holder' => $method->card_holder_name,
                        'expiry' => $method->formatted_expiry,
                        'isDefault' => $method->is_default,
                        'brandName' => ucfirst($method->card_type),
                    ];
                } else {
                    return [
                        'id' => $method->id,
                        'type' => 'bank_account',
                        'bankName' => $method->bank_name,
                        'accountType' => $method->account_type,
                        'accountLastFour' => $method->account_last_four,
                        'routingLastFour' => $method->routing_last_four,
                        'isDefault' => $method->is_default,
                    ];
                }
            });

        return response()->json([
            'success' => true,
            'payment_methods' => $paymentMethods
        ]);
    }

    /**
     * Store a new payment method
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $validated = $request->validate([
            'type' => 'required|in:card,bank_account',
            'card_type' => 'required_if:type,card',
            'last_four' => 'required_if:type,card|size:4',
            'card_holder_name' => 'required_if:type,card',
            'expiry_month' => 'required_if:type,card|size:2',
            'expiry_year' => 'required_if:type,card|size:4',
            'bank_name' => 'required_if:type,bank_account',
            'account_type' => 'required_if:type,bank_account',
            'account_last_four' => 'required_if:type,bank_account|size:4',
            'routing_last_four' => 'required_if:type,bank_account|size:4',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($request->is_default) {
            PaymentMethod::where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod = PaymentMethod::create(array_merge(
            $validated,
            ['user_id' => $user->id]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Payment method added successfully',
            'payment_method' => $paymentMethod
        ]);
    }

    /**
     * Update a payment method
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $paymentMethod = PaymentMethod::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($request->has('is_default') && $request->is_default) {
            // Unset other defaults
            PaymentMethod::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($request->only([
            'card_holder_name',
            'expiry_month',
            'expiry_year',
            'is_default'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully',
            'payment_method' => $paymentMethod
        ]);
    }

    /**
     * Delete a payment method
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $paymentMethod = PaymentMethod::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);
    }

    /**
     * Set a payment method as default
     */
    public function setDefault($id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Unset all defaults
        PaymentMethod::where('user_id', $user->id)
            ->update(['is_default' => false]);

        // Set this one as default
        $paymentMethod = PaymentMethod::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated'
        ]);
    }
}
