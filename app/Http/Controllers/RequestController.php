<?php
namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index() {
        return $this->user->request()->get();
    }

    public function store(Request $request) {
        $data = $request->only('user_id', 'gear_id', 'status');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $userRequest = \App\Models\Request::create($request->all());
        $userRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Request created successfully',
            'data' => $userRequest
        ], 201);
    }

    public function show($id) {
        $request = \App\Models\Request::find($id);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        return $request;
    }

    public function update(Request $request, $id) {
        $data = $request->only('user_id', 'gear_id', 'status');
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $userRequest = \App\Models\Request::findOrFail($id)->fill($request->all());
        $userRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully',
            'data' => $userRequest
        ]);
    }

    public function destroy($id) {
        \App\Models\Request::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully'
        ]);
    }

    public function rules() {
        return [
            'user_id' => 'required|integer',
            'gear_id' => 'required|integer',
            'status' => 'required|integer'
        ];
    }
}
