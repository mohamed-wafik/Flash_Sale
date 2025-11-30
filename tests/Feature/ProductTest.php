<?php

namespace Tests\Feature;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_api_returns_product_json()
    {
        $product = Product::factory()->create();
        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                    ->assertJson([
                        'id' => $product->id,
                        'title' => $product->title,
                        'initial_stock' => $product->initial_stock,
                        'available_stock' => $product->available_stock,
                        'price' => $product->price,
                    ]);

        $hold_res = $this->postJson("/api/holds", [
            'product_id' => $product->id,
            'qty' => 1,
        ]);

        $hold_res->assertStatus(201);
        $data = $hold_res->json();
        $holdUuid = $data['hold_id'];
        $this->assertNotEmpty($holdUuid);

        $product->refresh();
        $this->assertEquals(9, $product->available_stock);

        Hold::create([
            'uuid' => 'test-uuid-' . uniqid(),
            'product_id' => $product->id,
            'qty' => 2,
            'expires_at' => Carbon::now()->subMinutes(5),
            'used' => false,
            'released' => false
        ]);

        $this->artisan('app:release-expired-holds')->assertExitCode(0);

        $product->refresh();
        $this->assertEquals(11, $product->available_stock); // 9 + 2 from released hold
    }
}