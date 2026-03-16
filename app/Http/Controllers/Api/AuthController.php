<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            return $this->sendResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
            ], 'User registered successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Registration failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->sendError('Unauthorized', ['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();

            return $this->sendResponse([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
            ], 'Login successful.');
        } catch (\Exception $e) {
            return $this->sendError('Login failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function me()
    {
        try {
            return $this->sendResponse(auth()->user(), 'User retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving user', ['error' => $e->getMessage()], 500);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return $this->sendResponse([], 'Successfully logged out.');
        } catch (\Exception $e) {
            return $this->sendError('Logout failed', ['error' => $e->getMessage()], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = auth()->refresh();
            return $this->sendResponse([
                'access_token' => $token,
                'token_type' => 'bearer',
            ], 'Token refreshed successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Token refresh failed', ['error' => $e->getMessage()], 500);
        }
    }
}
