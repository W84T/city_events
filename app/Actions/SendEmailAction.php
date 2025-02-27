<?php

namespace App\Actions;

use App\Jobs\SendEmailJob;
use Exception;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class SendEmailAction
{

    public static function make(): Action
    {
        return Action::make('sendEmail')
            ->label(__('Send Email'))
            ->icon('heroicon-o-envelope')
            ->modalHeading('Compose Email')
            ->modalSubmitActionLabel('Send')
            ->form([
                TextInput::make('subject')
                    ->label(__('Email Subject'))
                    ->maxLength(255)
                    ->required(),
                Select::make('placeholder')
                    ->label(__('Insert Placeholder'))
                    ->options([
                        '{first_name}' => 'First Name',
                        '{last_name}' => 'Last Name',
                        '{title}' => 'Title',
                        '{job_title}' => 'Job Title',
                        '{mobile_number}' => 'Mobile Number',
                    ])
                    ->reactive() // Make the field reactive
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Get the current email body
                        $emailBody = $get('email_body');

                        // Append the selected placeholder to the email body
                        $set('email_body', $emailBody . ' ' . $state);
                    }),
                RichEditor::make('email_body')
                    ->label(__('Email Body'))
                    ->columnSpanFull()
                    ->required()
                    ->fileAttachmentsDisk('public') // Store images in public disk
                    ->fileAttachmentsDirectory('email-attachments') // Define folder
                    ->fileAttachmentsVisibility('public'), // Ensure they are accessible
            ])
            ->modalWidth('2xl')
            ->action(fn($record, array $data) => self::queueEmail($record, $data));
    }

    private static function queueEmail($record, $data)
    {
        $authUser = auth()->user();

        // Replace placeholders in the email body while preserving HTML
        $placeholders = [
            '{first_name}' => $record->first_name ?? '',
            '{last_name}' => $record->last_name ?? '',
            '{title}' => $record->title ?? '',
            '{job_title}' => $record->job_title ?? '',
            '{mobile_number}' => $record->mobile_number ?? '',
        ];

        $emailBody = str_replace(array_keys($placeholders), array_values($placeholders), $data['email_body']);

        try {
            dispatch(new SendEmailJob(
                $record->email,
                $data['subject'],
                $emailBody, // Send HTML email body
                $authUser
            ));

            Notification::make()
                ->title(__('Email Queued'))
                ->success()
                ->body(__('The email is being processed and will be sent shortly.'))
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title(__('Email Failed'))
                ->danger()
                ->body(__('Failed to queue the email: ') . $e->getMessage())
                ->send();
        }
    }


}
