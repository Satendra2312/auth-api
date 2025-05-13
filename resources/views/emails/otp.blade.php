@component('mail::message')
# Your OTP Code

Hello {{ $user->name }},

Your OTP code is: **{{ $otp }}**

This code will expire in 30 minutes.

If you did not request this OTP, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent