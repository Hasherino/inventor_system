<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Request extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'sender_id', 'gear_id', 'status',];

    public static function getUsersPendingRequests($id) {
        $requests = self::where('status', 0)->orWhere('status', 3)->get()->where('user_id', $id);

        foreach (self::all() as $request) {
            if ($request->gear()->first()->user_id == $id and $request->status == 2) {
                $requests = $requests->push($request);
            }
        }

        foreach($requests as $request) {
            $request['gear'] = $request->gear()->get();
        }

        return $requests;
    }

    public static function lendGear($request, $thisUser) {
        $data = $request->only('user_id', 'gear_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required|exists:users,id',
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        if ($request->user_id == $thisUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot lend gear to yourself.'
            ], 400);
        }

        $userGear = $thisUser->gear()->get()->where('lent', 0);
        $userGear = self::addLentGear($userGear, $thisUser);

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $gear = $userGear->find($gearId);
            if (!$gear) {
                $errors[] = 'Sorry, gear not found. (id: ' . $gearId .')';
            } elseif ($gear['current_holder'] != 1) {
                $errors[] = 'You do not currently hold this gear. (id: ' . $gearId .')';
            } elseif ($request->user_id == $gear->user_id) {
                $errors[] = 'This user owns this gear. (id: ' . $gearId . ')';
            } else {
                $gearRequest = self::where('gear_id', $gearId)->latest()->get()->first();
                if (!!$gearRequest and $gearRequest->status != 1) {
                    $errors[] = 'Gear already has a request. (id: ' . $gearId . ')';
                } elseif (!self::where('user_id', $request->user_id)->where('gear_id', $gearId)->get()->isEmpty()) {
                    $errors[] = 'User lent you this gear. (id: ' . $gearId . ')';
                } else {
                    self::create([
                        'user_id' => $request->user_id,
                        'sender_id' => $thisUser->id,
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
    }

    public static function returnGear($request, $thisUserId) {
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
            $requests = self::where('gear_id', $gearId)->get();
            $userRequest = $requests->where('user_id', $thisUserId)->where('created_at', $requests->max('created_at'))->first();

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
    }

    public static function acceptLendGetGear($request) {
        if (!$request or $request['status'] != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }
        $gear = $request->gear();

        $gear->update(['lent' => 1]);
        $request->update(['status' => 1]);

        return $gear;
    }

    public static function acceptReturnGetGear($request, $thisUserId) {
        if (!$request or $request->status != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }
        $user = $request->gear()->get()->first()->user()->get()->first()->id;
        if ($user != $thisUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $gear = $request->gear()->get()->first();
        $gear->update(['lent' => 0]);
        $request->delete();

        return $gear;
    }

    public static function declineReturn($request, $thisUserId) {
        if (!$request or $request->status != 2) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }
        $user = $request->gear()->get()->first()->user()->get()->first()->id;
        if ($user != $thisUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request->update(['status' => 1]);
    }

    public static function giveawayGear($request, $thisUser) {
        $data = $request->only('user_id', 'gear_id');
        $validator = Validator::make($data, [
            'user_id' => 'integer|required|exists:users,id',
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        if ($request->user_id == $thisUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot giveaway gear to yourself.'
            ], 400);
        }

        $errors = [];
        foreach ($request->gear_id as $gearId) {
            $gear = $thisUser->gear()->find($gearId);
            if (!!$error = self::statusCheck($gear, $gearId)) {
                $errors[] = $error;
            } else {
                self::create([
                    'user_id' => $request->user_id,
                    'sender_id' => $thisUser->id,
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
    }

    public static function acceptGiveaway($request, $thisUserId) {
        if (!$request or $request['status'] != 3) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, request not found.'
            ], 404);
        }

        $request->gear()->update(['user_id' => $thisUserId]);
    }

    public static function giveGearToYourself($request, $thisUserId) {
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
            if (!!$error = self::statusCheck($gear, $gearId)) {
                $errors[] = $error;
            } elseif ($gear->user_id == $thisUserId){
                $errors[] = 'You already own that gear. (id: ' . $gearId . ')';
            } else {
                $gear->update(['user_id' => $thisUserId]);
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }
    }

    public static function deleteRequest($id, $thisUser) {
        $request = $thisUser->request()->find($id);

        if (!$request) {
            $gears = $thisUser->gear()->get();
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

        if ($request->status == 1 or $request->status == 2) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete this request.'
            ], 400);
        }

        $request->delete();
    }

    private static function statusCheck($gear, $gearId) {
        if (!$gear) {
            return 'Sorry, gear not found. (id: ' . $gearId . ')';
        }

        if ($gear['lent'] == 1) {
            return 'You cannot give away lent gear. (id: ' . $gearId . ')';
        }

        if (!self::where('gear_id', $gearId)->get()->isEmpty()) {
            return 'Gear already has a request. (id: ' . $gearId . ')';
        }

        return null;
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function gear() {
        return $this->belongsTo(Gear::class);
    }
}
