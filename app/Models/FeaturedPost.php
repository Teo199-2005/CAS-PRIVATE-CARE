<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedPost extends Model
{
    protected $table = 'featured_posts';

    protected $fillable = [
        'image',
        'title',
        'caption',
        'link_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Full URL for the image (for API responses).
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image ? '/storage/' . ltrim($this->image, '/') : '';
    }
}
