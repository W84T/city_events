<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Number of retries

    public $email;
    public $subject;
    public $body;
    public $user;
    public function __construct($email, $subject, $body, $user)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->body = $body;
        $this->user = $user;
    }

    public function handle()
    {
        try {
            // Send the email with the HTML body and subject
            Mail::send([], [], function ($message) {
                $message->to($this->email)
                    ->subject($this->subject)
                    ->html($this->body);  // Use the html() method to send HTML body
            });

            if (!$this->user) return;

            Notification::make()
                ->title(__('Email Sent'))
                ->success()
                ->body(__('The email has been sent successfully to ') . $this->email)
                ->sendToDatabase($this->user);

        } catch (Exception $e) {
            // Log the error and send a failure notification
            Log::error("Failed to send email to {$this->email}: " . $e->getMessage());

            Notification::make()
                ->title(__('Email Failed'))
                ->danger()
                ->body(__('Failed to send email to ') . $this->email)
                ->sendToDatabase(auth()->user());
        }
    }
}
