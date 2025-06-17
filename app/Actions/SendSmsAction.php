<?php

namespace App\Actions;

use Filament\Actions\Action;
use App\Services\MoraSMSService;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class SendSmsAction
{
    public static function make(): Action
    {
        return Action::make('send_sms')
            ->label('Send SMS')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->modalHeading('Compose SMS')
            ->modalSubmitActionLabel('Send SMS')
            ->schema([
                MarkdownEditor::make('instructions')
                    ->label(__('Placeholder Instructions'))
                    ->default("You can use the following placeholders in your email:\n\n" .
                        "- **{title}** → Title\n" .
                        "- **{first_name}** → First Name\n" .
                        "- **{last_name}** → Last Name\n" .
                        "- **{company}** → company\n" .
                        "These placeholders will be automatically replaced when sending emails.")
                    ->disabled()
                    ->columnSpanFull(),
                Textarea::make('message')
                    ->label('Message')
                    ->required()
                    ->autosize()
                    ->columnSpanFull(),
            ])
            ->modalWidth('md')
            ->action(fn($record, array $data) => self::sendSms($record, $data));
    }

    private static function sendSms($record, $data)
    {
        // Replace placeholders with actual values
        $message = str_replace(
            ['{first_name}', '{last_name}', '{title}', '{company}'],
            [$record->first_name, $record->last_name, $record->title, $record->company],
            $data['message']
        );

        $smsService = new MoraSMSService();
        $response = $smsService->sendSMS($record->mobile_number, $message);

        if ($response['success']) {
            Notification::make()
                ->title('SMS Sent Successfully!')
                ->success()
                ->body("Message sent to {$record->first_name} ({$record->mobile_number}).\n💰 Remaining Balance: {$response['balance']}")
                ->send();
        } else {
            Notification::make()
                ->title('Failed to Send SMS')
                ->danger()
                ->body("Could not send SMS to {$record->first_name}. ❌ Error: {$response['message']}")
                ->send();
        }
    }
}
