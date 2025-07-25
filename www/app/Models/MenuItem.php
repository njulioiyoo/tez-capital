<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

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
            'parent_id' => $this->parent_id ? (string) $this->parent_id : null,
            'children' => $this->children->map(fn($child) => $child->toMenuArray())->toArray(),
            'is_separator' => $this->is_separator,
            'badge' => $this->badge,
            'disabled' => $this->disabled,
        ];
    }
}
