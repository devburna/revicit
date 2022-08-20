<?php

namespace App\Notifications;

use App\Enums\CampaignType;
use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\VonageMessage;

class Contact extends Notification implements ShouldQueue
{
    use Queueable;

    public $campaign, $meta;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->meta = json_decode($this->campaign->meta, true);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->campaign->type->is(CampaignType::MAIL_SMS())) {
            return ['mail', 'vonage'];
        } else if ($this->campaign->type->is(CampaignType::SMS())) {
            return ['vonage'];
        } else {
            return ['mail'];
        }
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
            ->from($this->meta['from']['email'], $this->meta['from']['name'])
            ->subject($this->meta['mail']['subject'])
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\VonageMessage
     */
    public function toVonage($notifiable)
    {
        return (new VonageMessage)
            ->clientReference($this->meta['from']['name'])
            ->content($this->meta['sms']['content'])
            ->from($this->meta['from']['phone']);
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
