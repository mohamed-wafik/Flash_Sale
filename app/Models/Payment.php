<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable = [
        'idempotency_key',
        'order_id',
        'status',
        'payload'
    ];
    protected $casts = [
        'payload' => 'array'
    ];

}