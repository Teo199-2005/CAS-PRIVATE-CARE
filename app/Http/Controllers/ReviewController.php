<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use App\Models\Caregiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Get all reviews for a caregiver
     */
    public function getCaregiverReviews($caregiverId)
    {
        try {
            $reviews = Review::where('caregiver_id', $caregiverId)
                ->with(['client:id,name', 'booking:id,service_type,service_date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($review) {
                    return [
                        'id' => $review->id,
                        'client_name' => $review->client->name ?? 'Anonymous',
                        'service_type' => $review->booking->service_type ?? 'N/A',
                        'service_date' => $review->booking->service_date ?? null,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'recommend' => $review->recommend,
                        'created_at' => $review->created_at->format('M d, Y'),
                    ];
                });

            $caregiver = Caregiver::find($caregiverId);
            
            return response()->json([
                'success' => true,
                'reviews' => $reviews,
                'average_rating' => $caregiver->rating ?? 0,
                'total_reviews' => $caregiver->total_reviews ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reviews for the authenticated client
     */
    public function getClientReviews()
    {
        try {
            $clientId = Auth::id();
            
            $reviews = Review::where('client_id', $clientId)
                ->with(['caregiver.user:id,name', 'booking:id,service_type,service_date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($review) {
                    return [
                        'id' => $review->id,
                        'booking_id' => $review->booking_id,
                        'caregiver_name' => $review->caregiver->user->name ?? 'Unknown',
                        'service_type' => $review->booking->service_type ?? 'N/A',
                        'service_date' => $review->booking->service_date ?? null,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'recommend' => $review->recommend,
                        'created_at' => $review->created_at->format('M d, Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if client can review a booking
     */
    public function canReview($bookingId)
    {
        try {
            $clientId = Auth::id();
            $booking = Booking::find($bookingId);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->client_id != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($booking->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'can_review' => false,
                    'message' => 'Booking must be completed before reviewing'
                ]);
            }

            // Get assigned caregivers
            // For completed bookings, get all assigned caregivers (status: 'assigned' or 'completed')
            $assignments = DB::table('booking_assignments')
                ->where('booking_id', $bookingId)
                ->whereIn('status', ['assigned', 'completed'])
                ->get();

            $caregiversToReview = [];
            foreach ($assignments as $assignment) {
                $existingReview = Review::where('booking_id', $bookingId)
                    ->where('caregiver_id', $assignment->caregiver_id)
                    ->first();

                if (!$existingReview) {
                    $caregiver = Caregiver::with('user:id,name')->find($assignment->caregiver_id);
                    if ($caregiver) {
                        $caregiversToReview[] = [
                            'id' => $caregiver->id,
                            'name' => $caregiver->user->name ?? 'Unknown',
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'can_review' => count($caregiversToReview) > 0,
                'caregivers' => $caregiversToReview,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check review status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit a review
     */
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'recommend' => 'required|boolean',
        ]);

        try {
            $clientId = Auth::id();
            $booking = Booking::find($request->booking_id);

            // Verify booking belongs to client
            if ($booking->client_id != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Verify booking is completed
            if ($booking->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot review incomplete booking'
                ], 400);
            }

            // Check if review already exists
            $existingReview = Review::where('booking_id', $request->booking_id)
                ->where('caregiver_id', $request->caregiver_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this caregiver for this booking'
                ], 400);
            }

            DB::beginTransaction();

            // Create review
            $review = Review::create([
                'booking_id' => $request->booking_id,
                'client_id' => $clientId,
                'caregiver_id' => $request->caregiver_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'recommend' => $request->recommend,
            ]);

            // Update caregiver rating
            $this->updateCaregiverRating($request->caregiver_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'review' => $review,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'recommend' => 'required|boolean',
        ]);

        try {
            $clientId = Auth::id();
            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            if ($review->client_id != $clientId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            DB::beginTransaction();

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'recommend' => $request->recommend,
            ]);

            // Update caregiver rating
            $this->updateCaregiverRating($review->caregiver_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'review' => $review,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        try {
            $clientId = Auth::id();
            $review = Review::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            if ($review->client_id != $clientId && Auth::user()->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            DB::beginTransaction();

            $caregiverId = $review->caregiver_id;
            $review->delete();

            // Update caregiver rating
            $this->updateCaregiverRating($caregiverId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all reviews (Admin only)
     */
    public function index()
    {
        try {
            // No authorization check - allow all authenticated users to view reviews
            
            $reviews = Review::with(['client:id,name', 'caregiver.user:id,name', 'booking:id,service_type,service_date'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($review) {
                    return [
                        'id' => $review->id,
                        'client_name' => $review->client->name ?? 'Unknown',
                        'caregiver_name' => $review->caregiver->user->name ?? 'Unknown',
                        'service_type' => $review->booking->service_type ?? 'N/A',
                        'service_date' => $review->booking->service_date ?? null,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'recommend' => $review->recommend,
                        'created_at' => $review->created_at->format('M d, Y H:i'),
                    ];
                });

            return response()->json([
                'success' => true,
                'reviews' => $reviews,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update caregiver's average rating and total reviews
     */
    private function updateCaregiverRating($caregiverId)
    {
        $stats = Review::where('caregiver_id', $caregiverId)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        Caregiver::where('id', $caregiverId)->update([
            'rating' => round($stats->avg_rating ?? 0, 2),
            'total_reviews' => $stats->total_reviews ?? 0,
        ]);
    }
}
