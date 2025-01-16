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
            // Validasi inputan
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => $request->has('provider') && $request->provider === 'google'
                    ? 'nullable'  // Password nullable jika menggunakan Google OAuth
                    : 'required|string|min:8',  // Password required jika menggunakan email/password
                'provider' => 'nullable|in:google', // Hanya menerima 'google' untuk Google OAuth
            ]);

            // Menyimpan pengguna
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : null, // Hash jika password ada
            ]);


            if ($request->provider === 'google') {
                $user->provider = 'google';
                $user->save();
            }

            // Login otomatis setelah registrasi
            auth()->login($user);

            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
            ], 201); // Status 201 Created
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi
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

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);


        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();


            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);
        }


        return response()->json([
            'message' => 'Invalid credentials. Please check your email or password.',
            'status' => 'error',
            'errors' => [
                'email' => 'The provided credentials do not match our records.',
            ]
        ], 401);
    }


   public function getUser()
   {
       $user = Auth::user();

       return response()->json([
           'message' => 'User retrieved successfully',
           'status' => 'success',
           'user' => $user,
       ]);
   }
}
