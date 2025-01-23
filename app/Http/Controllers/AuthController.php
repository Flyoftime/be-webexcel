<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    // Registrasi
    public function register(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => $request->has('provider') && $request->provider === 'google'
                    ? 'nullable'
                    : 'required|string|min:8',
                'provider' => 'nullable|in:google',
            ]);


            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : null,
            ]);


            if ($request->provider === 'google') {
                $user->provider = 'google';
                $user->save();
            }


            auth()->login($user);

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if($user) {
            if(Hash::check($request->password, $user->password)) {
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'message' => 'Login successful',
                    'status' => 'success',
                    'user' => $user,
                    'token' => $token,
                ]);
            } else {
                return response()->json([
                    'message' => 'Invalid credentials. Please check your email or password.',
                    'status' => 'error',
                    'errors' => [
                        'email' => 'The provided credentials do not match our records.',
                    ]
                ], 401);
            }
        } else {
            return response()->json([
                'message' => 'Invalid credentials. Please check your email or password.',
                'status' => 'error',
                'errors' => [
                    'email' => 'The provided credentials do not match our records.',
                ]
            ], 401);
        }
    }

    public function loginGoogle(Request $request) {
        $user = User::where('email', $request->email)->first();

        if($user && $user->provider == 'google'){
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'message' => 'Google account is not linked to your account',
                'status' => 'error',
                'errors' => [
                    'email' => 'The provided google account is not linked to your account',
                ]
            ], 403);
        }
    }

   public function getUser()
    {
        $user = User::all();

        return response()->json([
            'user' => $user,
        ]);
    }
    
}
