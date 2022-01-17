<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChangePasswordController
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function changePassword(Request $request) {
        $data = $request->only('password', 'confirm_password', 'old_password');
        $validator = Validator::make($data, [
            'password' => 'string|min:6',
            'confirm_password' => 'string',
            'old_password' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        if($request->password != $request->confirm_password) {
            return response()->json([
                'success' => false,
                'message' => 'Passwords do not match'
            ], 400);
        } else if (!Hash::check($request->old_password, $this->user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 400);
        } else {
            $this->user->update(['password'=>bcrypt($request->password)]);
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        }
    }
}
