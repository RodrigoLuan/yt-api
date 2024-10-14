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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->has('email')) {
                return response()->json([
                    'error' => 'Email already registered.'
                ], 409); 
            }

            return response()->json($errors, 400); 
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token  // Alterando de 'token' para 'access_token'
        ], 201);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!$token = Auth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401); 
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }


    public function me()
    {
        return response()->json(Auth::user());
    }
}
