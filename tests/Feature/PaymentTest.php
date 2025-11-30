<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function test_api_returns_payment_webhook()
    {
        $product = Product::factory()->create();
        $holdResp = $this->postJson('/api/holds', ['product_id'=>$product->id,'qty'=>3])->assertStatus(201);
        $holdUuid = $holdResp->json('hold_id');

        $orderResp = $this->postJson('/api/orders', ['hold_id'=>$holdUuid])->assertStatus(201);
        $orderId = $orderResp->json('order_id');
        
        $key = 'idem-123';
        $payload = ['idempotency_key' => $key, 'order_id' => $orderId, 'status' => 'failed'];

        $this->postJson('/api/payments/webhook', $payload)->assertStatus(201);
        $this->postJson('/api/payments/webhook', $payload)->assertStatus(200); 

        $product->refresh();
        $this->assertEquals(10, $product->available_stock); 

        $order = Order::where('public_id', $orderId)->first();
        $this->assertEquals('cancelled', $order->status);
    
    }
}