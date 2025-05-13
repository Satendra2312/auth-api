<?php

namespace App\Services;

use App\Models\OTP;
use App\Models\User;
use App\Models\Role;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    public function createUser(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role_id' => Role::USER,
        ]);
    }

    public function generateOTP(User $user)
    {
        // Invalidate all previous OTPs
        OTP::where('user_id', $user->id)->update(['used_at' => now()]);

        $otp = OTP::create([
            'user_id' => $user->id,
            'code' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        return $otp;
    }

    public function verifyOTP(User $user, string $code)
    {
        $otp = OTP::where('user_id', $user->id)
            ->where('code', $code)
            ->where('used_at', null)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['used_at' => now()]);
        return true;
    }

    public function createPasswordResetToken(string $email)
    {
        $token = Str::random(60);

        PasswordResetToken::updateOrCreate(
            ['email' => $email],
            [
                'token' => $token,
                'expires_at' => Carbon::now()->addMinutes(60),
            ]
        );

        return $token;
    }

    public function validatePasswordResetToken(string $email, string $token)
    {
        return PasswordResetToken::where('email', $email)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function deletePasswordResetToken(string $email)
    {
        PasswordResetToken::where('email', $email)->delete();
    }
}
