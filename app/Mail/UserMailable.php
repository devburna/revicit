<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user, $template, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $template, $subject)
    {
        $this->user = $user;
        $this->template = $template;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.' . $this->template)->subject($this->subject)->with('user', $this->user);
    }
}
