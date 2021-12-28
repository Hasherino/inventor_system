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
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $search = str_replace(' ', '', $request->search);
        if(!$request->company) {
            $users = User::whereRaw('CONCAT(first_name, last_name) ilike ? ', '%' . $search . '%')->get();
        } else {
            $users = User::where('company_id', $request->company)->
                          whereRaw('CONCAT(first_name, last_name) ilike ? ', '%' . $search . '%')->get();
        }

        return $this->gearCount($users);
    }

    public function userIndex(Request $request) {
        $company = $this->user->company()->get()->first()->id;
        $search = str_replace(' ', '', $request->search);
        $users = User::where('company_id', $company)->
                       whereRaw('CONCAT(first_name, last_name) ilike ? ', '%' . $search . '%')->get();
        return $this->gearCount($users);
    }

    public function show($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }
        $user['gear_count'] = $user->gear()->count();
        return $user;
    }

    public function gearCount($users) {
        foreach($users as $user) {
            $user['gear_count'] = $user->gear()->count();
        }
        return $users;
    }

    public function register(Request $request) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'company_id' => 'required|integer',
            'role' => 'required|integer'
        ]);

        if($validator->fails()){
          return response()->json([
              'success' => false,
              'message' => 'The email has already been taken'
          ], 400);
        }

        User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt(Str::random(20))]
        ));

        app('App\Http\Controllers\PasswordResetRequestController')->sendMail($request->email);
        return response()->json([
            'message' => 'Password creation email has been sent.'
        ], 201);
    }

    public function update(Request $request, $id) {
        $data = $request->only('first_name', 'last_name', 'email', 'role');
        $validator = Validator::make($data, [
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'string|email|unique:users',
            'role' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        $user->fill($request->all());
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        if ($user->gear()->count() != 0 or $user->request()->where('status', 2)->count() != 0) {
            return response()->json([
                'success' => false,
                'message' => 'User cannot be deleted, because user has gear'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
