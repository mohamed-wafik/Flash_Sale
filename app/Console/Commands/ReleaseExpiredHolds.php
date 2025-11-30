<?php

namespace App\Console\Commands;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\api\ProductController as ApiProductController;

class ReleaseExpiredHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expired-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired holds and restore product availability';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        if (!Schema::hasTable('holds')) {
            $this->warn("Table 'holds' does not exist — skipping command.");
            return Command::SUCCESS;
        }

        if (!Schema::hasTable('products')) {
            $this->warn("Table 'products' does not exist — skipping command.");
            return Command::SUCCESS;
        }
        
        $now = Carbon::now();

        $processed = 0;

        Hold::where('expires_at', '<=', $now)
            ->where('released', false)
            ->where('used', false)
            ->chunkById(200, function ($holds) use (&$processed) {
                foreach ($holds as $hold) {
                    DB::transaction(function () use ($hold) {
                        $product = Product::lockForUpdate()->find($hold->product_id);

                        if ($product) {
                            $product->available_stock += $hold->qty;
                            $product->save();

                            ApiProductController::flushProductCache($product->id);
                        }

                        $hold->released = true;
                        $hold->save();
                    });

                    $processed++;
                }
            });

        $this->info('Processed '.$processed.' expired holds.');
        return Command::SUCCESS;
    }
}