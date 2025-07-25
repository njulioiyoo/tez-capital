<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class MenuItem extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'title',
        'href',
        'icon',
        'position',
        'parent_id',
        'badge',
        'disabled',
        'is_separator',
        'is_active',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'is_separator' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * @api
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @api
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }

    /**
     * @api
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @api
     */
    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @api
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    public static function getMenuTree()
    {
        return self::active()
            ->rootItems()
            ->ordered()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->get();
    }

    public function toMenuArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'href' => $this->href,
            'icon' => $this->icon,
            'position' => $this->position,
            'parent_id' => $this->parent_id !== null ? (string) $this->parent_id : null,
            'children' => $this->children->map(fn ($child) => $child->toMenuArray())->toArray(),
            'is_separator' => $this->is_separator,
            'badge' => $this->badge,
            'disabled' => $this->disabled,
        ];
    }
}
