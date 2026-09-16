<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CronReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $sender;
    public $subject;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sender, $subject, $body)
    {
        $this->sender = $sender;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @return MailMessage
     */
    public function toMail()
    {
        return (new MailMessage)->from($this->sender[0], $this->sender[1]);
    }

    /**
     * @return CronReminder
     */
    public function build()
    {
        return $this
            ->subject($this->subject)
            ->view('vendor.notifications.email')
            ->subject($this->subject)
            ->with([
                "level" => "default",
                "greeting" => "Hello!",
                "introLines" => [
                    $this->body
                ],
                "actionText" => "View all jobs",
                "actionUrl" => url('/driver/jobs'),
                "outroLines" => []
            ]);
    }
}
