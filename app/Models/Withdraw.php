<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    protected $guarded = ['id'];
    public function balance()
    {
        return $this->belongsTo(Balance::class);
    }
}
