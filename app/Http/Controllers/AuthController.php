<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Registro de novo usuário.
     */
    public function register(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Se a validação falhar
        if ($validator->fails()) {
            $errors = $validator->errors();

            // Verifica se o erro é relacionado ao campo de email já registrado
            if ($errors->has('email')) {
                return response()->json([
                    'error' => 'Email already registered.'
                ], 409); // Retorna 409 Conflict
            }

            return response()->json($errors, 400); // Para outros erros de validação, retorna 400
        }

        // Criação do novo usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Gera o token JWT para o novo usuário
        $token = JWTAuth::fromUser($user);

        // Retorna a resposta de sucesso com o token JWT
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201); // Retorna 201 Created
    }

    /**
     * Login do usuário.
     */
    public function login(Request $request)
    {
        // Valida os campos de email e senha
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401); // 401 Unauthorized
        }

        // Retorna o token JWT se as credenciais forem válidas
        return $this->respondWithToken($token);
    }

    /**
     * Logout do usuário.
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Retorna o token JWT.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    /**
     * Retorna o usuário autenticado.
     */
    public function me()
    {
        return response()->json(Auth::user());
    }
}
