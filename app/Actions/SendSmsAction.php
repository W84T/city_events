<?php
namespace App\Actions;

use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use App\Services\MoraSMSService;
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
            ->form([
                Textarea::make('message')
                    ->label('Message')
                    ->helperText('Use placeholders: {first_name}, {last_name}, {title}, {company}')
                    ->required()
                    ->autosize()
                    ->columnSpanFull(),
            ])
            ->modalWidth('md')
            ->action(fn ($record, array $data) => self::sendSms($record, $data));
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
