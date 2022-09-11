<?php

namespace App\Notifications;

use App\Models\StorefrontOrderHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StorefrontOrderStatus extends Notification implements ShouldQueue
{
    use Queueable;

    public $history;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(StorefrontOrderHistory $history)
    {
        $this->history = $history;
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
            ->subject($this->history->comment)
            ->greeting("Hi {$notifiable->first_name} {$notifiable->last_name},")
            ->line("{$this->history->comment}")
            ->line('Kindly find a summary below.')
            ->action('Track my order', url("/storefront/track-order/{$this->history->order->reference}"));
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
