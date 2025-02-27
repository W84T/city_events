<?php
namespace App\Actions;

use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use App\Services\MoraSMSService;
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
                Textarea::make('message')
                    ->label('Message')
                    ->helperText('Use placeholders: {first_name}, {last_name}, {title}, {company}')
                    ->required()
                    ->autosize()
                    ->columnSpanFull(),
            ])
            ->modalWidth('md')
            ->action(fn ($records, array $data) => self::confirmAndSend($records, $data));
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
            ->action(fn () => self::sendSms($records, $data['message']));
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
