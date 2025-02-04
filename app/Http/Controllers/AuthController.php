<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\TwilioServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function register(Request $request, TwilioServices $twilioService)
    {
        try {
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => $request->has('provider') && $request->provider === 'google' ? 'nullable' : 'required|string|min:8',
                'provider' => 'nullable|in:google',
                'phone' => 'required|unique:users|regex:/^\+?[1-9]\d{1,14}$/', 
            ]);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : null,
                'phone' => $validated['phone'],
                'provider' => $request->provider ? $validated['provider'] : null,  
            ]);

            $verificationCode = rand(100000, 999999);

            Cache::put('verification_code_' . $validated['phone'], $verificationCode, 300); // 5 minutes expiration

            $twilioService->sendWhatsAppMessage($validated['phone'], "Your verification code is: $verificationCode");

            return response()->json([
                'message' => 'User registered successfully. Please check WhatsApp for the verification code.',
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

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric|digits:6',
        ]);

        $storedCode = $request->session()->get('verification_code');
        $phone = $request->session()->get('phone');

        if ($request->verification_code == $storedCode) {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                auth()->login($user);

                return response()->json([
                    'message' => 'Phone number verified successfully.',
                    'user' => $user,
                ], 200);
            }

            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Invalid verification code.',
        ], 400);
    }


    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
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

    public function loginGoogle(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && $user->provider == 'google') {
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

    public function editUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:Admin,User,Seller',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }
}
