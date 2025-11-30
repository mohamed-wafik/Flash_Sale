<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Hold;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //
    public function store(Request $request) {
        $validated = $request->validate([
            'hold_id' => 'required|exists:holds,uuid'
        ]);

        $hold = Hold::where('uuid', $validated['hold_id'])->firstOrFail();

        if ($hold->used) {
            return response()->json(['message' => 'Hold already used'], 400);
        }
        if ($hold->released) {
            return response()->json(['message' => 'Hold already released'], 400);
        }
        if ($hold->expires_at && $hold->expires_at->isPast()) {
            return response()->json(['message' => 'Hold expired'], 400);
        }

        return DB::transaction(function () use ($hold) {

            $order = Order::create([
                'product_id' => $hold->product_id,
                'qty' => $hold->qty,
                'status' => 'pending'
            ]);

            $hold->used = true;
            $hold->order_id = $order->id;
            $hold->save();

            return response()->json([
                'order_id' => $order->public_id,
                'status' => $order->status
            ], 201);
        });
    }

}