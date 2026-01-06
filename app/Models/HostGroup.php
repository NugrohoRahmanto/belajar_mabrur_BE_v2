<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'host',
        'name',
        'is_active',
        'is_default',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'is_active'  => 'bool',
        'is_default' => 'bool',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'group_id', 'group_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'group_id', 'group_id');
    }

    public static function resolveByHost(?string $host): ?self
    {
        if (!$host) {
            return null;
        }

        $host = strtolower($host);

        $direct = static::whereRaw('LOWER(host) = ?', [$host])
            ->active()
            ->first();

        if ($direct) {
            return $direct;
        }

        return static::whereJsonContains('metadata->aliases', $host)
            ->active()
            ->first();
    }

    public function aliases(): array
    {
        return $this->metadata['aliases'] ?? [];
    }

    public function toAliasArray(array $aliases): array
    {
        return array_values(array_unique(array_filter(array_map('strtolower', $aliases))));
    }

    public static function resolveByGroup(?string $groupId): ?self
    {
        if (!$groupId) {
            return null;
        }

        return static::where('group_id', $groupId)->active()->first();
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->active()->first();
    }
}
