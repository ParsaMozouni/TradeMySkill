<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;   // ✅ REQUIRED
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model   // ✅ MUST EXTEND Model
{
    protected $fillable = [
        'name',
        'skill_id',      // parent ID if subcategory
        'icon_path',
        'is_it_popular',
        'emoji'
    ];

    protected $casts = [
        'is_it_popular' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Skill::class, 'skill_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
