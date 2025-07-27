<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Education extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'title_id',
        'title_en',
        'description_id',
        'description_en',
        'content_id',
        'content_en',
        'image',
        'category',
        'tags',
        'is_published',
        'published_at',
        'meta_title_id',
        'meta_title_en',
        'meta_description_id',
        'meta_description_en',
        'sort_order',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    // Scope for published content
    /**
     * @api
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    // Scope for filtering by category
    /**
     * @api
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Get title based on current language
    /**
     * @api
     */
    public function getLocalizedTitle($language = 'id')
    {
        return $language === 'en' ? $this->title_en : $this->title_id;
    }

    // Get description based on current language
    /**
     * @api
     */
    public function getLocalizedDescription($language = 'id')
    {
        return $language === 'en' ? $this->description_en : $this->description_id;
    }

    // Get content based on current language
    /**
     * @api
     */
    public function getLocalizedContent($language = 'id')
    {
        return $language === 'en' ? $this->content_en : $this->content_id;
    }

    // Get meta title based on current language
    /**
     * @api
     */
    public function getLocalizedMetaTitle($language = 'id')
    {
        return $language === 'en' ? $this->meta_title_en : $this->meta_title_id;
    }

    // Get meta description based on current language
    /**
     * @api
     */
    public function getLocalizedMetaDescription($language = 'id')
    {
        return $language === 'en' ? $this->meta_description_en : $this->meta_description_id;
    }

    // Increment view count
    public function incrementViews()
    {
        $this->increment('view_count');
    }

    // Available categories
    public static function getCategories()
    {
        return [
            'tutorial' => 'Tutorial',
            'guide' => 'Guide',
            'tips' => 'Tips & Tricks',
            'news' => 'News',
            'announcement' => 'Announcement',
        ];
    }
}
