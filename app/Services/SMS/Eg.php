<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Eg
{
    protected string $username;
    protected string $password;
    protected string $sender;
    protected int $language;
    protected string $baseUrl;

    public function __construct()
    {
        $this->username = config('sms.eg.user_name');
        $this->password = config('sms.eg.password');
        $this->sender = config('sms.eg.sender_name');
        $this->language = (int)config('sms.eg.language');
        $this->baseUrl = config('sms.eg.base_url');
    }

    public function sendMessage(string $mobile, string $message, int $environment = 1)
    {
        try {

            $response = Http::asForm()->post("https://plus.smssmartegypt.com/api/PlusSMS/SendSMS?username={$this->username}&password={$this->password}&sendername={$this->sender}&mobiles=2$mobile&message=$message", [
                'username' => $this->username,
                'password' => $this->password,
                'sendername' => $this->sender,
                'mobiles' => '2' . $mobile,
                'message' => $message,
            ]);


            if ($response->successful()) {
                $data = $response->json();

                if (isset($data[0]) && $data[0]['type'] == "success")
                    return true;
                else
                    return false;
            } else {
                return false;
            }

        } catch (\Throwable $e) {
            Log::error('SMS sending failed', ['error' => $e->getMessage()]);
            return false;
        }


    }

    public function sendOTP(string $mobile, string $code, int $environment = 1): bool
    {
        $message = "Your OTP code is: $code";
        return $this->sendMessage($mobile, $message, $environment);
    }
}
