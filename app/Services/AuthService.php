<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Create a new user and return a token.
     */
    public function register(array $data): string
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'user',
        ]);

        return Auth::guard('api')->login($user);
    }

    /**
     * Authenticate a user and return a token.
     */
    public function login(array $credentials): ?string
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return null;
        }

        return $token;
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * Refresh the current token.
     */
    public function refresh(): string
    {
        return Auth::guard('api')->refresh();
    }

    /**
     * Get the authenticated user.
     */
    public function getAuthUser(): ?User
    {
        return Auth::guard('api')->user();
    }
}
