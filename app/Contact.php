<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contact extends Model
{
    protected $guarded = [];

    protected $dates = ['birthday'];

    public function setBirthdayAttribute($birthday)
    {
        // $this->attributes: array lives inside our model
        $this->attributes['birthday'] = Carbon::parse($birthday);
    }
}