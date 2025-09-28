<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Misr
{
    protected string $username;
    protected string $password;
    protected string $sender;
    protected int $language;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = config('sms.misr.user_name');
        $this->password = config('sms.misr.password');
        $this->sender = config('sms.misr.sender_name');
        $this->language = (int)config('sms.misr.language');
        $this->baseUrl = config('sms.misr.base_url');
    }

    public function sendMessage(string $mobile, string $message, int $environment = 1): bool
    {
        $payload = [
            'environment' => $environment,
            'username' => $this->username,
            'password' => $this->password,
            'sender' => $this->sender,
            'mobile' => '+2' . $mobile,
            'language' => $this->language,
            'message' => $message,
        ];

        try {
            $response = Http::acceptJson()->post($this->baseUrl, $payload);
            $data = $response->json();

            if (isset($data['code']) && $data['code'] == 1901) {
                return true;
            }

            Log::warning('SMS not sent', ['response' => $data]);
        } catch (\Throwable $e) {
            Log::error('SMS sending failed', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function sendOTP(string $mobile, string $code, int $environment = 1): bool
    {
        $message = "Your OTP code is: $code";
        return $this->sendMessage($mobile, $message, $environment);
    }
}
