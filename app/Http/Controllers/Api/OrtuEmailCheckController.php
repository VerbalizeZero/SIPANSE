<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrtuEmailCheckController extends Controller
{
    /**
     * Check email of Orang Tua by NISN
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'nisn' => ['required', 'regex:/^\d+$/', 'max:20'],
        ]);

        // Find user by NISN and role orang_tua
        $user = \App\Models\User::where('nisn', $request->nisn)
            ->where('role', 'orang_tua')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'email' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'email' => $user->email,
        ]);
    }
}