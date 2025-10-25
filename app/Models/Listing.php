<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = ['user_id','desired_skill_id','description'];

    public function user()
    {
        // eager load skills for card pills
        return $this->belongsTo(User::class)->with('skills');
    }

    public function desiredSkill()
    {
        return $this->belongsTo(\App\Models\Skill::class, 'desired_skill_id')->with('parent');
    }
}
