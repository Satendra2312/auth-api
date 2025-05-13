<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\SendOTPEmail;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    use ApiResponser;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->createUser($request->validated());

        // Generate signed URL for email verification
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Dispatch job to send verification email
        SendVerificationEmail::dispatch($user, $verificationUrl);

        // Generate OTP
        $otp = $this->authService->generateOTP($user);
        SendOTPEmail::dispatch($user, $otp->code);

        return $this->success([
            'user' => $user,
            'verification_url' => $verificationUrl,
        ], 'User registered successfully. Please check your email for verification.', 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return $this->error('Email not verified', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully');
    }

    public function user(Request $request)
    {
        return $this->success($request->user(), 'User retrieved successfully');
    }
}
