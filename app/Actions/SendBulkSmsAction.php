<?php

namespace App\Actions;

use Filament\Actions\BulkAction;
use App\Services\MoraSMSService;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class SendBulkSmsAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('send_bulk_sms')
            ->label('Send Bulk SMS')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->modalHeading('Compose Bulk SMS')
            ->modalSubmitActionLabel('Next')
            ->form([
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
            ->action(fn($records, array $data) => self::confirmAndSend($records, $data));
    }

    private static function confirmAndSend($records, $data)
    {
        $messagePreview = "";
        foreach ($records as $record) {
            $personalizedMessage = str_replace(
                ['{first_name}', '{last_name}', '{title}', '{company}'],
                [$record->first_name, $record->last_name, $record->title, $record->company],
                $data['message']
            );

            $messagePreview .= "{$record->first_name}: {$personalizedMessage}\n";
        }

        return BulkAction::modal()
            ->heading('Confirm Bulk SMS')
            ->description(nl2br($messagePreview)) // Preview personalized messages
            ->icon('heroicon-o-check')
            ->modalSubmitActionLabel('Send SMS')
            ->action(fn() => self::sendSms($records, $data['message']));
    }

    private static function sendSms($records, $messageTemplate)
    {
        $smsService = new MoraSMSService();
        $numbers = [];
        $failed = 0;

        foreach ($records as $record) {
            $personalizedMessage = str_replace(
                ['{first_name}', '{last_name}', '{title}', '{company}'],
                [$record->first_name, $record->last_name, $record->title, $record->company],
                $messageTemplate
            );

            $numbers[] = $record->mobile_number;

            $response = $smsService->sendSMS($record->mobile_number, $personalizedMessage);

            if (!$response['success']) {
                $failed++;
            }
        }

        $total = count($records);
        $successful = $total - $failed;
        $balance = $smsService->checkBalance();

        Notification::make()
            ->title('Bulk SMS Sent')
            ->success()
            ->body("Sent: {$successful}/{$total} messages.\nRemaining Balance: {$balance}")
            ->send();
    }
}
