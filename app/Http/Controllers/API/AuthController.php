<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/login",
        tags: ["Auth"],
        summary: "Login",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", example: "admin@gmail.com"),
                    new OA\Property(property: "password", type: "string", example: "password")
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Login success")]
    )]
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->validated())) {
            return ApiResponse::error('email atau password salah', 401);
        }

        $user = Auth::user()->load(['jabatan', 'unit']);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => $user
        ]);
    }

    #[OA\Get(
        path: "/api/me",
        tags: ["Auth"],
        summary: "Get profile",
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function me()
    {
        return ApiResponse::success(
            auth()->user()->load(['jabatan', 'unit'])
        );
    }

    #[OA\Post(
        path: "/api/logout",
        tags: ["Auth"],
        summary: "Logout",
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'logout berhasil');
    }
}
