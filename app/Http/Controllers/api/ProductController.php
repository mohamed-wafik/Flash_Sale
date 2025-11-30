<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    //
    public function show($id) {
        $cacheKey = "product:{$id}:summary";

        $product = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($id) {
            return Product::select('id','title','price','initial_stock','available_stock')->findOrFail($id);
        });

        return response()->json($product);
    }

    /**
     * Flush product cache for the given product id.
     */
    public static function flushProductCache(int $productId): void
    {
        $cacheKey = "product:{$productId}:summary";
        Cache::forget($cacheKey);
    }
}