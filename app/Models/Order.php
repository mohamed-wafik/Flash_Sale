<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    //
    protected $fillable = ['public_id','product_id','qty','status'];
    protected static function boot() {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->public_id)) $m->public_id = Str::orderedUuid()->toString();
        });
    }
}