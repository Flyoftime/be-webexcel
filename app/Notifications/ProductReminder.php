<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductReminder extends Notification
{
    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail']; // Menggunakan email untuk notifikasi
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Pengingat: Produk Anda Akan Dihapus')
            ->greeting('Halo, ' . $notifiable->name)
            ->line("Produk Anda **{$this->product->name}** belum ada pembelian dalam 25 hari.")
            ->line('Produk Anda akan dihapus dari sistem dalam waktu 5 hari jika tidak ada pembelian.')
            ->action('Lihat Produk Anda', url('/products/' . $this->product->id))
            ->line('Harap pastikan produk Anda menarik perhatian pelanggan.');
    }
}
