<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Gear;
use App\Models\History;
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
        $validator = Validator::make($data, [
            'user_id' => 'required|integer',
            'gear_id' => 'required|integer',
            'status' => 'required|integer'
        ]);

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

    public function lend(Request $request, $id) {
        $data = $request->only('user_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = $this->user->gear()->find($id);
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        if(!\App\Models\Request::where('gear_id', $id)->get()->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Gear already has a request'
            ], 400);
        }

        \App\Models\Request::create([
            'user_id' => $request->user_id,
            'gear_id' => $id,
            'status' => 0
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Lend request sent.'
        ]);
    }

    public function acceptLend($id) {
        $request = $this->user->request()->find($id);
        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request->gear()->update(['lent' => 1]);
        $request->update(['status' => 1]);
        History::create([
            'user_id' => $this->user->id,
            'gear_id' => $request->gear()->get()->first()->id,
            'event' => 0
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Lend request accepted.'
        ]);
    }

    public function update(Request $request, $id) {
        $userRequest = $this->user->request()->find($id);

        if (!$userRequest) {
            $gears = $this->user->gear()->get();
            foreach($gears as $gear) {
                $userRequest = $gear->request()->find($id);
                if ($userRequest) {
                    break;
                }
            }
        }

        if (!$userRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $data = $request->only('status');
        $validator = Validator::make($data, [
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $userRequest->fill($request->all());
        $userRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully',
            'data' => $userRequest
        ]);
    }

    public function destroy($id) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $request = $this->user->request()->find($id);

        if (!$request) {
            $gears = $this->user->gear()->get();
            foreach($gears as $gear) {
                $request = $gear->request()->find($id);
                if ($request) {
                    break;
                }
            }
        }

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully'
        ]);
    }
}
