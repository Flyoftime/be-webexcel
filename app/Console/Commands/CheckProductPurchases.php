<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Notifications\ProductReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckProductPurchases extends Command
{
    protected $signature = 'products:check-purchases';
    protected $description = 'Send reminders for products without purchases and delete them after 30 days';

    public function handle()
    {
        $now = now();

        // Ambil semua produk milik seller
        $products = Product::whereHas('owner', function ($query) {
            $query->where('role_id', 2); // Role 2 = Seller
        })->get();

        foreach ($products as $product) {
            $daysSinceLastPurchase = $product->last_purchased_at
                ? $now->diffInDays($product->last_purchased_at)
                : 30;

            // Kirim notifikasi jika sudah 25 hari
            if ($daysSinceLastPurchase == 25) {
                Notification::send($product->owner, new ProductReminder($product));
                $this->info('Reminder sent for product ID: ' . $product->id);
            }

            // Hapus produk jika sudah 30 hari
            if ($daysSinceLastPurchase >= 30) {
                $product->delete();
                $this->info('Deleted product ID: ' . $product->id);
            }
        }

        $this->info('Processed reminders and deletions for products.');
    }
}
