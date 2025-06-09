<?php

namespace App\Application\Services\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AdminAuthService
{
    /**
     * Login admin.
     */
    public function login(string $email, string $password): array
    {
        $admin = Admin::where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if admin is suspended
        if ($admin->status === Admin::STATUS_SUSPENDED) {
            throw new \Exception('Account is suspended. Please contact administrator.', 403);
        }

        // Check if admin has been converted
        if ($admin->status === Admin::STATUS_CONVERTED) {
            throw new \Exception('Account has been converted to tenant. Please use your school domain to login.', 403);
        }

        // Delete all existing tokens
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken('admin-token', ['admin'], Carbon::now()->addDays(30))->plainTextToken;

        // Update status from pending to active if first login
        if ($admin->status === Admin::STATUS_PENDING) {
            $admin->update(['status' => Admin::STATUS_ACTIVE]);
        }

        return [
            'admin' => $admin->fresh(),
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(30),
        ];
    }

    /**
     * Logout admin.
     */
    public function logout(string $token): void
    {
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if ($accessToken) {
            $accessToken->delete();
        }
    }

    /**
     * Change password.
     */
    public function changePassword(Admin $admin, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password is incorrect.'],
            ]);
        }

        $admin->update([
            'password' => Hash::make($newPassword),
        ]);

        // Delete all existing tokens to force re-login
        $admin->tokens()->delete();
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Admin $admin): array
    {
        // Delete all existing tokens
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken('admin-token', ['admin'], Carbon::now()->addDays(30))->plainTextToken;

        return [
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(30),
        ];
    }

    /**
     * Generate temporary password.
     */
    public function generateTemporaryPassword(): string
    {
        return 'temp_' . now()->format('Ymd') . '_' . rand(1000, 9999);
    }    /**
     * Send welcome email with credentials.
     */
    public function sendWelcomeEmail(Admin $admin, string $temporaryPassword): void
    {
        try {
            Mail::to($admin->email)->send(new \App\Mail\Admin\AdminWelcomeEmail($admin, $temporaryPassword));
        } catch (\Exception $e) {
            // Log email sending failure but don't fail the admin creation
            Log::warning('Failed to send welcome email to admin', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordResetEmail(Admin $admin, string $temporaryPassword): void
    {
        try {
            Mail::to($admin->email)->send(new \App\Mail\Admin\AdminWelcomeEmail($admin, $temporaryPassword));
        } catch (\Exception $e) {
            Log::warning('Failed to send password reset email to admin', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}
