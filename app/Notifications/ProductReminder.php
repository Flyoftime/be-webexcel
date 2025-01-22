<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProductReminder extends Notification
{
    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Product Reminder')
            ->line('Hi ' . $notifiable->name . ',')
            ->line('The product "' . $this->product->name . '" has not been purchased for 25 days.')
            ->line('Please update the product details and ensure it is still available for purchase.');
    }
}

