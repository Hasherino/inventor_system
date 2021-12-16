<?php
namespace App\Http\Controllers;

use App\Models\Gear;
use Illuminate\Http\Request;
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

    public function index() {
        return $this->user->gear()->get();
    }

    public function store(Request $request) {
        $data = $request->only('name', 'serial_number', 'quantity', 'unit_price', 'long_term', 'lend_stage');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = $this->user->gear()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Gear added successfully',
            'data' => $gear
        ], 201);
    }

    public function show($id) {
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
        $data = $request->only('name', 'serial_number', 'quantity', 'unit_price', 'long_term', 'lend_stage');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = gear::findOrFail($id)->fill($request->all());
        $gear->save();

        return response()->json([
            'success' => true,
            'message' => 'Gear updated successfully',
            'data' => $gear
        ]);
    }

    public function destroy($id) {
        gear::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gear deleted successfully'
        ]);
    }

    public function rules() {
        return [
            'name' => 'required|string',
            'serial_number' => 'required',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'long_term' => 'required|boolean',
            'lend_stage' => 'required|integer'
        ];
    }
}
