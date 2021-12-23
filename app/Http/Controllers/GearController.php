<?php
namespace App\Http\Controllers;

use App\Models\Gear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class GearController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function userIndex() {
        $userGear = $this->user->gear()->get();
        foreach ($userGear as $gear) {
            $gear['own'] = 1;
        }

        $requests = $this->user->request()->where('status', 1);
        foreach ($requests as $request) {
            $gear = $request->gear()->first();
            $gear['own'] = 0;
            $userGear = $userGear->push($gear);
        }

        $userGear = $this->groupByCode($userGear);

        return $userGear;
    }

    public function index() {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }
        return $this->groupByCode(gear::all());
    }

    public function store(Request $request) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $data = $request->only('name', 'serial_number', 'unit_price', 'long_term', 'user_id');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'serial_number' => 'string',
            'unit_price' => 'required|numeric',
            'long_term' => 'required|boolean',
            'user_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = gear::create($request->all());
        $gear->save();

        return response()->json([
            'success' => true,
            'message' => 'Gear added successfully',
            'data' => $gear
        ], 201);
    }

    public function show($id) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $gear = gear::find($id);

        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        return $gear;
    }

    public function userShow($id) {
        $gear = $this->user->gear()->find($id);

        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        return $gear;
    }

    public function update(Request $request, $id) {
        $data = $request->only('name', 'serial_number', 'unit_price', 'long_term', 'lend_stage', 'user_id');
        $validator = Validator::make($data, [
            'name' => 'string',
            'serial_number' => 'string',
            'unit_price' => 'numeric',
            'long_term' => 'boolean',
            'lend_stage' => 'integer',
            'user_id' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = gear::find($id);
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        $gear->fill($request->all());
        $gear->save();

        return response()->json([
            'success' => true,
            'message' => 'Gear updated successfully',
            'data' => $gear
        ]);
    }

    public function destroy($id) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $gear = gear::find($id);
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        $gear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gear deleted successfully'
        ]);
    }

    public function groupByCode($userGear) {
        $userGear = $userGear->groupBy('code')->values();
        $final = [];
        foreach ($userGear as $group) {
            $gearCollection = collect($group);
            $gear = $group->first();
            $name = $gear->name;
            $code = $gear->code;
            $count = $group->count();
            $final[] = collect(['name' => $name, 'code' => $code, 'count' => $count, 'gear' => $gearCollection]);
        }

        return $final;
    }
}
