<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->get('username'))->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => 'The credentials are incorrect.'
            ]);
        }

        if ($user->isAdmin) {
            return [
             'access_token_admin' => $user->createToken($user->name.$user->created_at, ['client:admin'])->plainTextToken,
            ];
        } else {
            return [
                'access_token_user' => $user->createToken($user->name.$user->created_at, ['client:index'])->plainTextToken,
            ];
        }
    }

    public function logout()
    {
        $user = auth()->user();

        /* Apaga somente o token utilizado nesta seção */
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked.'], JsonResponse::HTTP_OK);
    }
}
