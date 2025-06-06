<?php

namespace App\Application\Services\Superadmin;

use App\Domain\Superadmin\Models\Superadmin;
use App\Domain\Superadmin\Repositories\SuperadminRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class SuperadminAuthService
{
    public function __construct(
        private SuperadminRepositoryInterface $superadminRepository
    ) {}

    /**
     * Authenticate superadmin and return token.
     */
    public function login(string $email, string $password): array
    {
        $superadmin = $this->superadminRepository->findByEmail($email);

        if (!$superadmin || !Hash::check($password, $superadmin->password)) {
            throw new \Exception('Invalid credentials', 401);
        }

        if (!$superadmin->is_active) {
            throw new \Exception('Account is inactive', 403);
        }

        // Update last login
        $superadmin->updateLastLogin();

        // Create token with 7-day expiration
        $token = $superadmin->createToken(
            'superadmin-token',
            ['superadmin'],
            now()->addDays(7)
        );

        return [
            'superadmin' => $superadmin,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }

    /**
     * Logout superadmin by revoking current token.
     */
    public function logout(string $tokenId): bool
    {
        $token = PersonalAccessToken::findToken($tokenId);
        
        if ($token) {
            $token->delete();
            return true;
        }

        return false;
    }

    /**
     * Get authenticated superadmin.
     */
    public function getAuthenticatedSuperadmin(string $tokenId): ?Superadmin
    {
        $token = PersonalAccessToken::findToken($tokenId);
        
        if ($token && $token->tokenable instanceof Superadmin) {
            return $token->tokenable;
        }

        return null;
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Superadmin $superadmin): array
    {
        // Revoke existing tokens
        $superadmin->tokens()->delete();

        // Create new token
        $token = $superadmin->createToken(
            'superadmin-token',
            ['superadmin'],
            now()->addDays(7)
        );

        return [
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }
}
