<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkEmailMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $subject;
    public string $body;

    public function __construct($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('emails.bulk-email')
            ->with(['body' => $this->body]);
    }
}
