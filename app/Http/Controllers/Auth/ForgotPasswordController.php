<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\SendPasswordResetEmail;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\URL;

class ForgotPasswordController extends Controller
{
    use ApiResponser;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $token = $this->authService->createPasswordResetToken($request->email);

        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['token' => $token, 'email' => $request->email]
        );

        SendPasswordResetEmail::dispatch($user, $resetUrl);

        return $this->success(null, 'Password reset link sent to your email');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        if (!$this->authService->validatePasswordResetToken($request->email, $request->token)) {
            return $this->error('Invalid or expired token', 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->update(['password' => bcrypt($request->password)]);
        $this->authService->deletePasswordResetToken($request->email);

        return $this->success(null, 'Password reset successfully');
    }
}
