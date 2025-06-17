<?php

namespace App\Jobs;

use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $emails;
    public $subject;
    public $emailBodies; // Array of email bodies (one for each recipient)
    public $user;

    public function __construct($emails, $subject, $emailBodies, $user)
    {
        $this->emails = $emails;
        $this->subject = $subject;
        $this->emailBodies = $emailBodies;
        $this->user = $user;
    }

    public function handle()
    {
        $successCount = 0;
        $failedEmails = [];

        foreach ($this->emails as $index => $email) {
            try {
                $emailBody = $this->emailBodies[$index]; // Get the corresponding email body

                Mail::send([], [], function ($message) use ($email, $emailBody) {
                    $message->to($email)
                        ->subject($this->subject)
                        ->html($emailBody);  // Use html() to send HTML body
                });

                $successCount++;
            } catch (Exception $e) {
                $failedEmails[] = ['email' => $email, 'error' => $e->getMessage()];
            }
        }

        $this->sendSummaryNotification($successCount, $failedEmails);
    }

    private function sendSummaryNotification($successCount, $failedEmails)
    {
        if (!$this->user) return;

        $failedCount = count($failedEmails);
        $message = "Email delivery complete!<br>$successCount emails were sent successfully";

        if ($failedCount > 0) {
            $message .= "<br>Failed to send $failedCount emails. Review the error logs and try again.";
            $csvPath = $this->generateFailedEmailsCSV($failedEmails);
        }

        $notification = Notification::make()
            ->title(__('Email Send'))
            ->body($message);

        if ($failedCount > 0) {
            $notification->warning();
            $notification->actions([
                Action::make('downloadFailedEmails')
                    ->label('Download Failed Emails CSV')
                    ->url($csvPath)
                    ->button(),
            ]);
        } else {
            $notification->success();
        }

        $notification->sendToDatabase($this->user);
    }

    private function generateFailedEmailsCSV($failedEmails)
    {
        $csvData = "Email,Error Message\n";
        foreach ($failedEmails as $failed) {
            $csvData .= "{$failed['email']},\"{$failed['error']}\"\n";
        }

        $filePath = "failed-emails/failed-emails-" . now()->timestamp . ".csv";
        Storage::disk('public')->put($filePath, $csvData);

        return "storage/" . $filePath;
    }
}
