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
        $requests = $this->user->request()->get();
        foreach($requests as $request) {
            $request['gear'] = $request->gear()->get();
        }
        return $requests;
    }

    public function show($id) {
        $request = \App\Models\Request::find($id);

        if (!$request) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request['gear'] = $request->gear()->get();

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

        if ($request->user_id == $this->user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot lend gear to yourself.'
            ], 400);
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

    public function returnLend($id) {
        $request = $this->user->request()->where('gear_id', $id)->first();
        if (!$request or $request->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $gear = $request->gear()->first();
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        $gear->update(['lent' => 0]);
        $request->delete();
        History::create(['gear_id' => $gear->id, 'user_id' => $this->user->id, 'event' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Gear returned'
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
