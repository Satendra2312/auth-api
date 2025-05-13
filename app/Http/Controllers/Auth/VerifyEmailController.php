<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    use ApiResponser;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!hash_equals((string) $request->route('hash'), sha1($user->email))) {
            return $this->error('Invalid verification link', 403);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email already verified', 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success(null, 'Email verified successfully');
    }

    public function verifyWithOTP(VerifyEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        if ($user->hasVerifiedEmail()) {
            return $this->error('Email already verified', 400);
        }

        if (!$this->authService->verifyOTP($user, $request->code)) {
            return $this->error('Invalid or expired OTP', 400);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success(null, 'Email verified successfully');
    }
}
