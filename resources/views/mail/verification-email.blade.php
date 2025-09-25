<x-mail::message>
# Email Verification

Your OTP code is: **{{ $otp }}**

Please use this code to verify your email address.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
