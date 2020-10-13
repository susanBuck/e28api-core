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
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $response = [
                'success' => false,
                'errors' => ['These credentials do not match our records']
            ];
        } else {
            $token = $user->createToken(config('app.name'))->plainTextToken;

            $response = [
                'success' => true,
                'user' => $user,
                'token' => $token
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
            'message' => ['Registration failed'],
            'errors' => $validator->errors()
        ], 409); # 409 Conflict
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password)
        ]);
       
        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
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