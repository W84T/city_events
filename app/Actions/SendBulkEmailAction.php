<?php

namespace App\Actions;

use App\Jobs\SendBulkEmailJob;
use Exception;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;

class SendBulkEmailAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('send_bulk_email')
            ->label(__('Send Bulk Email'))
            ->icon('heroicon-o-envelope')
            ->modalHeading('Compose Bulk Email')
            ->modalSubmitActionLabel('Send Emails')
            ->form([
                \Filament\Forms\Components\Section::make(__('How to Use Placeholders'))
                    ->collapsed()
                    ->schema([
                        \Filament\Forms\Components\MarkdownEditor::make('instructions')
                            ->label(false)
                            ->default("You can use the following placeholders in your email:\n\n" .
                                "- **{title}** → Title\n" .
                                "- **{first_name}** → First Name\n" .
                                "- **{last_name}** → Last Name\n" .
                                "- **{job_title}** → Job Title\n" .
                                "- **{mobile_number}** → Mobile Number\n\n" .
                                "These placeholders will be automatically replaced when sending emails.")
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                TextInput::make('subject')
                    ->label(__('Email Subject'))
                    ->maxLength(255)
                    ->required(),
                RichEditor::make('email_body')
                    ->label(__('Email Body'))
                    ->columnSpanFull()
                    ->required()
                    ->fileAttachmentsDisk('public') // Store images in public disk
                    ->fileAttachmentsDirectory('email-attachments') // Define folder
                    ->fileAttachmentsVisibility('public'), // Ensure they are accessible
            ])
            ->modalWidth('2xl')
            ->action(fn($records, array $data) => self::queueEmails($records, $data));
    }

    private static function queueEmails($records, $data)
    {
        $authUser = auth()->user(); // Get the authenticated user
        $emails = $records->pluck('email')->toArray(); // Collect all emails

        try {
            // Replace placeholders in the email body for each record
            $emailBodies = [];
            foreach ($records as $record) {
                $placeholders = [
                    '{first_name}' => $record->first_name ?? '',
                    '{last_name}' => $record->last_name ?? '',
                    '{title}' => $record->title ?? '',
                    '{job_title}' => $record->job_title ?? '',
                    '{mobile_number}' => $record->mobile_number ?? '',
                ];

                $emailBody = str_replace(array_keys($placeholders), array_values($placeholders), $data['email_body']);
                $emailBodies[] = $emailBody;
            }

            // Dispatch the bulk email job
            dispatch(new SendBulkEmailJob($emails, $data['subject'], $emailBodies, $authUser))
                ->onQueue('default');

            Notification::make()
                ->title(__('Bulk Email Queued'))
                ->success()
                ->body("Emails are being processed in the background.")
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Bulk Email Failed'))
                ->danger()
                ->body("Failed to queue emails: " . $e->getMessage())
                ->send();
        }
    }
}
