<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingChecklistItem extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
