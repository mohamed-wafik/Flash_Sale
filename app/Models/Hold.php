<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Hold extends Model
{
    //
    protected $fillable = [
        'uuid',
        'product_id',
        'qty',
        'expires_at',
        'used',
        'released',
        'order_id'
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
        'released' => 'boolean'
    ];
    public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) $model->uuid = (string) Str::uuid();
        });
    }
    public function product(){
         return $this->belongsTo(Product::class); 
    }
    public function order(){ 
        return $this->belongsTo(Order::class); 
    }
}