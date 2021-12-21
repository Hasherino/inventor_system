<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class PasswordResetRequestController extends Controller {

    public function sendPasswordResetEmail(Request $request){
        // If email does not exist
        if(!$this->validEmail($request->email)) {
            return response()->json([
                'message' => 'Email does not exist.'
            ], Response::HTTP_NOT_FOUND);
        } else {
            // If email exists
            $this->sendMail($request->email);
            return response()->json([
                'message' => 'Password reset email has been sent.'
            ], Response::HTTP_OK);
        }
    }

    public function sendMail($email){
        $token = $this->generateToken($email);
        Mail::to($email)->send(new SendMail($token, $email));
    }

    public function validEmail($email) {
        return !!User::where('email', $email)->first();
    }

    public function generateToken($email){
        $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

        if($isOtherToken) {
            return $isOtherToken->token;
        }

        $token = Str::random(80);;
        $this->storeToken($token, $email);
        return $token;
    }

    public function storeToken($token, $email){
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
        ]);
    }

}
