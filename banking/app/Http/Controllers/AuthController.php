<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //register user fonction
    public function register(Request $request)
    {
        //validate data
        $validator = Validator::make($request->all(), [
            'name'=>'required|string',
            'firstname'=>'string|max:20',
            'phone'=>'string|max:13',
            'adress'=>'string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string|min:6',
            'role' => 'required|string|in:admin,user,'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)] // Crypter le mot de passe
        ));
        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;
        $user->remember_token = $token;
        $user->save();

        return response()->json(['message' => 'Login successful', 'token' => $token, 'user'=>$user]);
    }

    public function logout(Request $request)
    {
        $user = User::where('remember_token', $request->bearerToken())->first();
        $user->tokens()->delete();
        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
