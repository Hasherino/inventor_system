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

    public function pendingRequests() {
        $requests = \App\Models\Request::where('status', 0)->orWhere('status', 3)->get()->where('user_id', $this->user->id);

        foreach (\App\Models\Request::all() as $request) {
            if ($request->gear()->first()->user_id == $this->user->id and $request->status == 2) {
                $requests = $requests->push($request);
            }
        }

        foreach($requests as $request) {
            $request['gear'] = $request->gear()->get();
        }
        return $requests->sortByDesc('created_at')->values();
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

    public function lend(Request $request) {
        $data = $request->only('user_id', 'gear_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required|exists:users,id',
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
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

        $userGear = $this->user->gear()->get()->where('lent', 0);
        $userGear = app('App\Http\Controllers\GearController')->addLentGear($userGear);

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $gear = $userGear->find($gearId);

            if (!$gear) {
                $errors[] = 'Sorry, gear not found. (id: ' . $gearId .')';
            } elseif ($request->user_id == $gear->user_id) {
                $errors[] = 'This user owns this gear. (id: ' . $gearId . ')';
            } else {
                $gearRequest = \App\Models\Request::where('gear_id', $gear->id)->get();
                if (!$gearRequest->isEmpty() and $gearRequest->first()->status != 1) {
                    $errors[] = 'Gear already has a request. (id: ' . $gearId . ')';
                } else {
                    \App\Models\Request::create([
                        'user_id' => $request->user_id,
                        'sender_id' => $this->user->id,
                        'gear_id' => $gearId,
                        'status' => 0
                    ])->save();
                }
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }

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
        $gear = $request->gear();

        $gear->update(['lent' => 1]);
        $request->update(['status' => 1]);
        History::create([
            'user_id' => $this->user->id,
            'sender_id' => $request->sender_id,
            'owner_id' => $gear->get()->first()->user_id,
            'gear_id' => $gear->get()->first()->id,
            'event' => 0
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Lend request accepted.'
        ]);
    }

    public function returnLend(Request $request) {
        $data = $request->only('gear_id');
        $validator = Validator::make($data, [
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $requests = \App\Models\Request::where('gear_id', $gearId)->get();
            $userRequest = $requests->where('user_id', $this->user->id)->where('created_at', $requests->max('created_at'))->first();

            if (!$userRequest) {
                $errors[] = 'Sorry, request not found. (id: ' . $gearId . ')';
            } elseif ($userRequest->status == 2) {
                $errors[] = 'Return request is already sent. (id: ' . $gearId . ')';
            } elseif ($userRequest->status != 1) {
                $errors[] = 'Gear is not in lent stage. (id: ' . $gearId . ')';
            } elseif (!$userRequest->gear()->first()) {
                $errors[] = 'Sorry, gear not found. (id: ' . $gearId . ')';
            } else {
                $userRequest->update(['status' => 2]);
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }

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
            'user_id' => $request->sender_id,
            'owner_id' => $gear->user_id,
            'sender_id' => $request->user_id,
            'event' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gear returned'
        ]);
    }

    public function declineReturnLend($id) {
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

        $request->update(['status' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Return request declined'
        ]);
    }

    public function giveaway(Request $request) {
        $data = $request->only('user_id', 'gear_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required',
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $gear = $this->user->gear()->find($gearId);
            if (!!$error = $this->statusCheck($gear, $gearId)) {
                $errors[] = $error;
            } else {
                \App\Models\Request::create([
                    'user_id' => $request->user_id,
                    'sender_id' => $this->user->id,
                    'gear_id' => $gearId,
                    'status' => 3
                ])->save();
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }

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
        $request->gear()->update(['user_id' => $this->user->id]);
        $request->delete();
        History::create([
            'user_id' => $this->user->id,
            'sender_id' => $request->sender_id,
            'owner_id' => $request->sender_id,
            'gear_id' => $request->gear()->get()->first()->id,
            'event' => 2
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request accepted.'
        ]);
    }

    public function giveawayToYourself(Request $request) {
        if(!!$error = $this->authorityCheck())
            return $error;

        $data = $request->only('gear_id');
        $validator = Validator::make($data, [
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $gear = Gear::where('id', $gearId)->get()->first();
            if (!!$error = $this->statusCheck($gear, $gearId)) {
                $errors[] = $error;
            } elseif ($gear->user_id == $this->user->id){
                $errors[] = 'You already own that gear. (id: ' . $gearId . ')';
            } else {
                $gear->update(['user_id' => $this->user->id]);
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear ownership changed.'
        ]);
    }

    public function destroy($id) {
        $request = $this->user->request()->find($id);

        if (!$request) {
            $gears = $this->user->gear()->get();
            foreach($gears as $gear) {
                $request = $gear->request()->find($id);
                if (!!$request) {
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

        if ($request->status == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this request'
            ], 400);
        }

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully'
        ]);
    }

    public function statusCheck($gear, $gearId) {
        if (!$gear) {
            return 'Sorry, gear not found. (id: ' . $gearId . ')';
        }

        if ($gear['lent'] == 1) {
            return 'You cannot give away lent gear. (id: ' . $gearId . ')';
        }

        if (!\App\Models\Request::where('gear_id', $gearId)->get()->isEmpty()) {
            return 'Gear already has a request. (id: ' . $gearId . ')';
        }

        return null;
    }
}
