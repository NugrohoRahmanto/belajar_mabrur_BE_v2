<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'name',
        'category',
        'group_id',
        'arabic',
        'latin',
        'translate_id',
        'description',
    ];

    protected $hidden = [
        'group_id',
    ];

    public function scopeForGroup(Builder $query, string $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function hostGroup()
    {
        return $this->belongsTo(HostGroup::class, 'group_id', 'group_id');
    }
}
