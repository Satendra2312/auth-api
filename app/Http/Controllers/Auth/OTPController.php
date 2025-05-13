<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOTPRequest;
use App\Jobs\SendOTPEmail;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponser;

class OTPController extends Controller
{
    use ApiResponser;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function resend(ResendOTPRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email already verified', 400);
        }

        $otp = $this->authService->generateOTP($user);
        SendOTPEmail::dispatch($user, $otp->code);

        return $this->success(null, 'OTP resent successfully');
    }
}
