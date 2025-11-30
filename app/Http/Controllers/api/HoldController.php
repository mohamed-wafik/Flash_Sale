<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Controllers\api\ProductController as ApiProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HoldController extends Controller
{
    public function store(Request $request) {

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);

        return DB::transaction(function () use ($validated) {
            
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            if ($product->available_stock < $validated['qty']) {
                return response()->json(['message' => 'Insufficient stock'], 400);
            }

            $product->available_stock -= $validated['qty'];
            $product->save();

            // Invalidate product cache so API returns fresh stock values
            ApiProductController::flushProductCache($product->id);

            $expiresAt = Carbon::now()->addMinutes(2);

            $hold = $product->holds()->create([
                'qty' => $validated['qty'],
                'expires_at' => $expiresAt
            ]);

            return response()->json([
                'hold_id' => $hold->uuid,
                'expires_at' => $hold->expires_at->toIso8601String()
            ], 201);
        });
    }

}