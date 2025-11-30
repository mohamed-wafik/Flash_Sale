<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\api\ProductController as ApiProductController;

class PaymentController extends Controller
{
    //
    public function handle(Request $request) {

        $validated = $request->validate([
            'idempotency_key' => 'required|string',
            'order_id' => 'required|exists:orders,public_id',
            'status' => 'required|in:success,failed',
            'payload' => 'nullable|array'
        ]);

        return DB::transaction(function () use ($validated, $request) {

            $existing = Payment::where('idempotency_key', $validated['idempotency_key'])->first();
            if ($existing) {
                return response()->json(['status' => $existing->status], 200);
            }

            $order = Order::where('public_id', $validated['order_id'])->first();

            if (!$order) {
                return response()->json(['status' => 'deferred'], 202);
            }

            $payment = Payment::create([
                'idempotency_key' => $validated['idempotency_key'],
                'order_id' => $order->id,
                'status' => $validated['status'],
                'payload' => $validated['payload'] ?? $request->all()
            ]);

            if ($validated['status'] === 'success') {

                $order->update(['status' => 'paid']);

            } else {

                $order->update(['status' => 'cancelled']);

                $hold = Hold::where('order_id',$order->id)->first();
                if ($hold && !$hold->released) {

                    $product = Product::lockForUpdate()->find($hold->product_id);

                    $product->available_stock += $hold->qty;
                    $product->save();

                    ApiProductController::flushProductCache($product->id);

                    $hold->released = true;
                    $hold->save();
                }
            }

            return response()->json(['status' => $payment->status], 201);

        });
    }

}