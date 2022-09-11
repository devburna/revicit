<?php

namespace App\Notifications;

use App\Models\StorefrontOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StorefrontOrderInvoice extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(StorefrontOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Your receipt from {$notifiable->storefront->company->name} (ID: {$this->order->reference})")
            ->greeting("We've received your order")
            ->line("Hi {$notifiable->first_name} {$notifiable->last_name}, thank you for ordering from {$notifiable->storefront->name} by {$notifiable->storefront->company->name}! 🎉")
            ->line('Kindly find a summary below.')
            ->action('Track my order', url("/storefront/track-order/{$this->order->reference}"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
