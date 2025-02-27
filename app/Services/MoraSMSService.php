<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoraSMSService
{
    protected $apiKey;
    protected $username;
    protected $sender;

    public function __construct()
    {
        $this->apiKey = env('MORA_API_KEY');
        $this->username = env('MORA_USERNAME');
        $this->sender = env('MORA_SENDER');
    }

    /**
     * Send SMS using Mora API
     */
    public function sendSMS($to, $message)
    {
        try {
            $numbers = is_array($to) ? implode(',', $to) : $to; // Support multiple numbers

            $response = Http::get("https://www.mora-sa.com/api/v1/sendsms", [
                'api_key'  => $this->apiKey,
                'username' => $this->username,
                'message'  => $message,
                'sender'   => $this->sender,
                'numbers'  => $numbers,
                'return'   => 'json',
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['data']['code']) && $result['data']['code'] == 100) {
                    Log::info("SMS sent successfully to {$numbers}");

                    $balance = $this->checkBalance();
                    return [
                        'success' => true,
                        'message' => "SMS sent successfully!",
                        'balance' => $balance,
                    ];
                } else {
                    Log::error("Mora SMS Error: " . json_encode($result));
                    return [
                        'success' => false,
                        'message' => "Failed to send SMS. Error: " . ($result['status']['message'] ?? "Unknown error"),
                    ];
                }
            }

            Log::error("Mora SMS Request Failed: " . $response->body());
            return [
                'success' => false,
                'message' => "Failed to send SMS. HTTP Error",
            ];
        } catch (\Exception $e) {
            Log::error("Mora SMS Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Exception occurred: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Check balance from MORA API
     */
    public function checkBalance()
    {
        try {
            $response = Http::get("https://www.mora-sa.com/api/v1/balance", [
                'api_key'  => $this->apiKey,
                'username' => $this->username,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['data']['balance'])) {
                    return $result['data']['balance'];
                } else {
                    Log::error("Mora Balance API Error: Unexpected response format - " . json_encode($result));
                    return false;
                }
            }

            Log::error("Mora Balance Check Failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Mora Balance Check Exception: " . $e->getMessage());
            return false;
        }
    }
}
