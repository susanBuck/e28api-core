<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;
use Auth;

class AuthController extends Controller
{
    /**
     *
     */
    public function auth(Request $request)
    {
        $response = [
            'loggedIn' => $request->user() ? true : false,
            'user' => $request->user()
        ];
        
        return response($response, 200);
    }

    /**
     * POST /login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
            'message' => ['Login failed'],
            'errors' => $validator->errors()->all()
            ], 200);
        }

        $authed = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($authed) {
            $user = User::where('email', $request->email)->first();

            // Delete any existing tokens this user may have
            $user->tokens()->delete();

            // Create them a new token
            $token = $user->createToken(config('app.name'))->plainTextToken;

            $response = [
                'success' => true,
                'user' => $user,
                'token' => $token
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Login failed',
                'errors' => ['These credentials do not match our records'],
                'test' => 'login-failed-bad-credentials'
            ];
        }

        return response($response, 200);
    }

    /**
     *
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response([
            'success' => false,
            'errors' => $validator->errors()->all(),
            'test' => 'registration-failed'
        ], 200);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password)
        ]);
       
        $token = $user->createToken(config('app.name'))->plainTextToken;

        $response = [
            'success' => true,
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201); # 201 created
    }

    /**
     * GET /api/logout
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
            $response = ['success' => true, 'message' => 'Logout succesful'];
        } else {
            $response = ['success' => false, 'message' => 'User not logged in'];
        }

        return response($response, 200); # 200
    }
}