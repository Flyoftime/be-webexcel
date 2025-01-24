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
    {$now = now();

        $products = Product::whereHas('user', function ($query) {
            $query->where('role', 2);
        })->get();

        foreach ($products as $product) {
            $lastPurchasedDays = $product->last_purchased_at
                ? $now->diffInDays($product->last_purchased_at)
                : 30;

            // Kirim pengingat pada hari ke-25
            if ($lastPurchasedDays == 25) {
                $product->owner->notify(new ProductReminder($product));
                $this->info("Pengingat dikirim untuk produk {$product->name}.");
            }

            // Hapus produk pada hari ke-30
            if ($lastPurchasedDays >= 30) {
                $product->delete();
                $this->info("Produk {$product->name} telah dihapus karena tidak ada pembelian dalam 30 hari.");
            }
        }
        
    }

}
