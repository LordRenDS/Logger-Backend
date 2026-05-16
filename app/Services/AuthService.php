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

        return Auth::login($user);
    }

    /**
     * Authenticate a user and return a token.
     */
    public function login(array $credentials): ?string
    {
        if (!$token = Auth::attempt($credentials)) {
            return null;
        }

        return $token;
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Refresh the current token.
     */
    public function refresh(): string
    {
        return Auth::refresh();
    }

    /**
     * Get the authenticated user.
     */
    public function getAuthUser(): ?User
    {
        return Auth::user();
    }
}
