<?php

namespace App\Http\Controllers;

use App\Models\FeaturedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeaturedPostController extends Controller
{
    /**
     * List featured posts for client dashboard (active only, ordered).
     */
    public function index()
    {
        $posts = FeaturedPost::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'image_url' => $p->image_url,
                'title' => $p->title,
                'caption' => $p->caption,
                'link_url' => $p->link_url,
            ]);

        return response()->json(['featured_posts' => $posts]);
    }

    /**
     * Admin: list all featured posts.
     */
    public function adminIndex()
    {
        $posts = FeaturedPost::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        $data = $posts->map(fn ($p) => [
            'id' => $p->id,
            'image_url' => $p->image_url,
            'image' => $p->image,
            'title' => $p->title,
            'caption' => $p->caption,
            'link_url' => $p->link_url,
            'sort_order' => $p->sort_order,
            'is_active' => $p->is_active,
            'created_at' => $p->created_at?->toIso8601String(),
        ]);

        return response()->json(['featured_posts' => $data]);
    }

    /**
     * Admin: create featured post (upload image + optional fields).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'link_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $file = $request->file('image');
        $path = $file->store('featured-posts', 'public');

        $post = FeaturedPost::create([
            'image' => $path,
            'title' => $validated['title'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'link_url' => $validated['link_url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Featured post created.',
            'featured_post' => [
                'id' => $post->id,
                'image_url' => $post->image_url,
                'image' => $post->image,
                'title' => $post->title,
                'caption' => $post->caption,
                'link_url' => $post->link_url,
                'sort_order' => $post->sort_order,
                'is_active' => $post->is_active,
                'created_at' => $post->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Admin: update featured post (optional new image and fields).
     */
    public function update(Request $request, $id)
    {
        $post = FeaturedPost::findOrFail($id);

        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
            'link_url' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            $path = $request->file('image')->store('featured-posts', 'public');
            $post->image = $path;
        }

        if (array_key_exists('title', $validated)) {
            $post->title = $validated['title'];
        }
        if (array_key_exists('caption', $validated)) {
            $post->caption = $validated['caption'];
        }
        if (array_key_exists('link_url', $validated)) {
            $post->link_url = $validated['link_url'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $post->sort_order = $validated['sort_order'];
        }
        if (array_key_exists('is_active', $validated)) {
            $post->is_active = $validated['is_active'];
        }

        $post->save();

        return response()->json([
            'message' => 'Featured post updated.',
            'featured_post' => [
                'id' => $post->id,
                'image_url' => $post->image_url,
                'image' => $post->image,
                'title' => $post->title,
                'caption' => $post->caption,
                'link_url' => $post->link_url,
                'sort_order' => $post->sort_order,
                'is_active' => $post->is_active,
                'created_at' => $post->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Admin: delete featured post and its image file.
     */
    public function destroy($id)
    {
        $post = FeaturedPost::findOrFail($id);
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return response()->json(['message' => 'Featured post deleted.']);
    }
}
