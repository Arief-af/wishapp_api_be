<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $table = 'savings';
    protected $guarded = ['id'];
    public function wish()
    {
        return $this->belongsTo(Wish::class);
    }
}
