<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DispositivoPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispositivoApiController extends Controller
{
    public function registrarToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token_fcm'  => 'required|string',
            'plataforma' => 'nullable|in:android,ios',
        ]);

        DispositivoPush::updateOrCreate(
            ['user_id' => auth()->id(), 'token_fcm' => $data['token_fcm']],
            ['plataforma' => $data['plataforma'] ?? 'android']
        );

        return response()->json(['ok' => true]);
    }
}
