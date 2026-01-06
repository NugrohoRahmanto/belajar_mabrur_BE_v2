<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGrowth extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'growth_date',
    ];

    protected $casts = [
        'growth_date' => 'date',
    ];

    public function scopeForGroup(Builder $query, string $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function hostGroup()
    {
        return $this->belongsTo(HostGroup::class, 'group_id', 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
