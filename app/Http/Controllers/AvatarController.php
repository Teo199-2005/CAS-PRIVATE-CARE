<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * AvatarController
 * 
 * Handles user avatar uploads.
 */
class AvatarController extends Controller
{
    /**
     * Upload user avatar
     */
    public function upload($id, Request $request)
    {
        try {
            $authUser = auth()->user();
            
            if (!$authUser) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Only allow users to update their own avatar (admins can update anyone)
            $isAdmin = in_array(strtolower((string) $authUser->user_type), ['admin', 'adminstaff'], true);
            
            if ((int) $authUser->id !== (int) $id && !$isAdmin) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $user = User::findOrFail($id);

            if (!$request->hasFile('avatar')) {
                return response()->json(['error' => 'No avatar file provided'], 400);
            }

            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $file = $request->file('avatar');

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $file->store('avatars', 'public');
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'avatar' => '/storage/' . $path,
                'message' => 'Avatar uploaded successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload avatar: ' . $e->getMessage()], 500);
        }
    }
}
