<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Session;

class AuthController extends Controller
{
    /**
     * POST /auth
     * When the client is first mounted, it pings this route to check its authentication status
     */
    public function auth(Request $request)
    {
        $response = [
            'success' => true,
            'authenticated' => $request->user() ? true : false,
            'user' => $request->user(),
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
            'success' => false,
            'errors' => $validator->errors()->all()
            ], 200);
        }

        $authed = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($authed) {
            $response = [
                'success' => true,
                'authenticated' => true,
                'user' => User::where('email', $request->email)->first(),
            ];
        } else {
            $response = [
                'success' => false,
                'errors' => ['These credentials do not match our records'],
                'test' => 'login-failed-bad-credentials'
            ];
        }

        return response($response, 200);
    }

    /**
     * POST /register
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
       
        Auth::login($user);

        $response = [
            'success' => true,
            'user' => $user,
        ];

        return response($response, 200);
    }

    /**
     * POST /logout
     */
    public function logout(Request $request)
    {
        Session::flush();

        return response([
            'success' => true,
            'authenticated' => false,
        ], 200);
    }
}