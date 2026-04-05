<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            return ApiResponse::error(
                'email atau password salah',
                401
            );
        }

        $user = Auth::user()->load([
            'position',
            'unit'
        ]);

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => $user
        ], 'login berhasil');
    }

    public function me()
    {
        $user = auth()->user()->load([
            'position',
            'unit'
        ]);

        return ApiResponse::success($user);
    }

    public function logout()
    {
        auth()
            ->user()
            ->currentAccessToken()
            ->delete();

        return ApiResponse::success(
            null,
            'logout berhasil'
        );
    }
}
