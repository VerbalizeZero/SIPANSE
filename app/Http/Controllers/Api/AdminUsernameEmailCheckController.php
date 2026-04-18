<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminUsernameEmailCheckController extends Controller
{
    /**
     * Check if admin username exists and return email.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->username)
            ->whereIn('role', ['tu', 'bendahara'])
            ->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'email' => $user->email,
                'name' => $user->name,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username tidak ditemukan.',
        ], 404);
    }

    /**
     * Check if admin email exists.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)
            ->whereIn('role', ['tu', 'bendahara'])
            ->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'username' => $user->username,
                'name' => $user->name,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email tidak ditemukan.',
        ], 404);
    }
}