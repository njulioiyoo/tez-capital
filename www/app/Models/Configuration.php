<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Configuration extends Model implements Auditable
{
    use AuditableTrait;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'is_public' => 'boolean',
        ];
    }

    const GROUP_GENERAL = 'general';

    const GROUP_BRANDING = 'branding';

    const GROUP_HOMEPAGE = 'homepage';

    const GROUP_CREDIT = 'credit';

    const GROUP_MAINTENANCE = 'maintenance';

    const GROUP_CONTACT = 'contact';

    const TYPE_STRING = 'string';

    const TYPE_TEXT = 'text';

    const TYPE_INTEGER = 'integer';

    const TYPE_BOOLEAN = 'boolean';

    const TYPE_JSON = 'json';

    const TYPE_FILE = 'file';

    const TYPE_EMAIL = 'email';

    const TYPE_URL = 'url';

    public static function get(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();

        if (! $config) {
            return $default;
        }

        return $config->getValue();
    }

    /**
     * @api
     */
    public static function set(string $key, $value, string $type = self::TYPE_STRING, string $group = self::GROUP_GENERAL): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    public function getValue()
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return (bool) $this->value;
            case self::TYPE_INTEGER:
                return (int) $this->value;
            case self::TYPE_JSON:
                return is_string($this->value) ? json_decode($this->value, true) : $this->value;
            case self::TYPE_FILE:
                return $this->value ? Storage::url($this->value) : null;
            default:
                return $this->value;
        }
    }

    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(function ($config) {
                return [$config->key => $config->getValue()];
            })
            ->toArray();
    }

    public static function getPublic(): array
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($config) {
                return [$config->key => $config->getValue()];
            })
            ->toArray();
    }

    public static function getAllGrouped(): array
    {
        return static::all()
            ->groupBy('group')
            ->map(function ($configs) {
                return $configs->mapWithKeys(function ($config) {
                    return [$config->key => [
                        'value' => $config->getValue(),
                        'type' => $config->type,
                        'description' => $config->description,
                        'is_public' => $config->is_public,
                    ]];
                })->all();
            })
            ->toArray();
    }
}
