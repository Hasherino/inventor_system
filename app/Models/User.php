<?php

namespace App\Models;

use App\Http\Controllers\UserController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'company_id',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getAllUsers($request) {
        $search = str_replace(' ', '', $request->search);
        $users = User::whereRaw('CONCAT(first_name, last_name) like ? ', '%' . $search . '%')->get();

        if(!!$request->company) {
            $users = $users->where('company_id', $request->company);
        }

        foreach($users as $user) {
            $user['gear_count'] = $user->gear()->count();
        }

        return $users->sortBy('first_name', SORT_NATURAL|SORT_FLAG_CASE)->values();
    }

    public static function getSpecificUser($id) {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        $user['gear_count'] = $user->gear()->count();

        return $user;
    }

    public static function createUser($request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'company_id' => 'required|integer',
            'role' => 'required|integer'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt(Str::random(20))]
        ));
    }

    public static function updateUser($request, $id, $userRole) {
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

        if(!!$request->role and $userRole != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        $user->fill($request->all());
        $user->save();

        return $user;
    }

    public static function deleteUser($id) {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        if(self::addLentGear($user->gear()->get(), $user)->count() != 0) {
            return response()->json([
                'success' => false,
                'message' => 'User cannot be deleted, because user has gear'
            ], 400);
        }

        $user->delete();
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function gear() {
        return $this->hasMany(Gear::class);
    }

    public function request() {
        return $this->hasMany(Request::class);
    }

    public function history() {
        return $this->hasMany(History::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role
        ];
    }
}
