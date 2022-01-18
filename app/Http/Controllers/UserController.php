<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index(Request $request) {
        $users = User::getAllUsers($request);

        return $users;
    }

    public function userIndex(Request $request) {
        $request->company = $this->user->company()->get()->first()->id;
        $users = User::getAllUsers($request);

        return $users;
    }

    public function show($id) {
        $user = User::getSpecificUser($id);

        return $user;
    }

    public function register(Request $request) {
        if (!!$error = User::createUser($request)) {
            return $error;
        }

        app('App\Http\Controllers\PasswordResetRequestController')->sendMail($request->email);

        return response()->json([
            'message' => 'Password creation email has been sent.'
        ], 201);
    }

    public function update(Request $request, $id) {
        $user = User::updateUser($request, $id, $this->user->role);

        if ($user instanceof Response) {
            return $user;
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy($id) {
        if (!!$error = User::deleteUser($id)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
