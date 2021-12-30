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
            $request['lender_id'] = $request['gear']->first()->user_id;
        }
        return $requests;
    }

    public function pendingLends() {
        $requests = $this->user->request()->where('status', 0)->get();
        foreach($requests as $request) {
            $request['gear'] = $request->gear()->get();
            $request['lender_id'] = $request['gear']->first()->user_id;
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
        $error = $this->statusCheck($gear);
        if (!!$error) {
            return $error;
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
        if (!$request or $request['status'] != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request->gear()->update(['lent' => 1]);
        $request->update(['status' => 1]);
        History::create([
            'user_id' => $this->user->id,
            'sender_id' => $request->gear()->get()->first()->user_id,
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

        $request->update(['status' => 2]);

        return response()->json([
            'success' => true,
            'message' => 'Return request created'
        ]);
    }

    public function acceptReturnLend($id) {
        $request = \App\Models\Request::find($id);
        if (!$request or $request->status != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }
        $user = $request->gear()->get()->first()->user()->get()->first()->id;
        if ($user != $this->user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $gear = $request->gear()->get()->first();
        $gear->update(['lent' => 0]);
        $request->delete();
        History::create([
            'gear_id' => $gear->id,
            'user_id' => $this->user->id,
            'sender_id' => $gear->user_id,
            'event' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gear returned'
        ]);
    }

    public function giveaway($id, Request $request) {
        $data = $request->only('user_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = $this->user->gear()->find($id);
        $error = $this->statusCheck($gear);
        if (!!$error) {
            return $error;
        }

        \App\Models\Request::create([
            'user_id' => $request->user_id,
            'gear_id' => $id,
            'status' => 3
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request sent.'
        ]);
    }

    public function acceptGiveaway($id) {
        $request = $this->user->request()->find($id);
        if (!$request or $request['status'] != 3) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }
        $ownerId = $request->gear()->get()->first()->user_id;
        $request->gear()->update(['user_id' => $this->user->id]);
        $request->delete();
        History::create([
            'user_id' => $this->user->id,
            'sender_id' => $ownerId,
            'gear_id' => $request->gear()->get()->first()->id,
            'event' => 2
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request accepted.'
        ]);
    }

    public function giveawayToYourself($id) {
        if ($this->user->role == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $gear = Gear::where('id', $id)->get()->first();
        $error = $this->statusCheck($gear);
        if (!!$error) {
            return $error;
        }
        if ($gear->user_id == $this->user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You already own that gear'
            ], 400);
        }

        $gear->update(['user_id' => $this->user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Gear ownership changed.'
        ], 200);
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

    public function statusCheck($gear) {
        if (!$gear) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, gear not found.'
            ], 404);
        }

        if ($gear['lent'] == 1) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot give away lent gear'
            ], 404);
        }

        if (!\App\Models\Request::where('gear_id', $gear->id)->get()->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Gear already has a request'
            ], 400);
        }
    }
}
