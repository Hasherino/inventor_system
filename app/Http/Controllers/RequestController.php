<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Gear;
use App\Models\History;
use App\Models\Request as UserRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function pendingRequests(Request $request) {
        return UserRequest::getUsersPendingRequests($request->user->id)->sortByDesc('created_at')->values();
    }

    public function lend(Request $request) {
        if (!!$error = UserRequest::lendGear($request)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Lend request sent.'
        ]);
    }

    public function acceptLend(Request $request, $id) {
        $user = $request->user;

        $request = $user->request()->find($id);
        $gear = UserRequest::acceptLendGetGear($request);

        if ($gear instanceof Response) {
            return $gear;
        }

        History::create([
            'user_id' => $user->id,
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
        if (!!$error = UserRequest::returnGear($request)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Return request created'
        ]);
    }

    public function acceptReturnLend(Request $request, $id) {
        $userRequest = UserRequest::find($id);
        $gear = UserRequest::acceptReturnGetGear($userRequest, $request->user->id);

        if ($gear instanceof Response) {
            return $gear;
        }

        History::create([
            'gear_id' => $gear->id,
            'user_id' => $userRequest->sender_id,
            'owner_id' => $gear->user_id,
            'sender_id' => $userRequest->user_id,
            'event' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gear returned'
        ]);
    }

    public function declineReturnLend(Request $request, $id) {
        if (!!($error = UserRequest::declineReturn(UserRequest::find($id), $request->user->id)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Return request declined'
        ]);
    }

    public function giveaway(Request $request) {
        if (!!($error = UserRequest::giveawayGear($request)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request sent.'
        ]);
    }

    public function acceptGiveaway(Request $request, $id) {
        $user = $request->user;

        $userRequest = $user->request()->find($id);

        if (!!($error = UserRequest::acceptGiveaway($userRequest, $user->id)) instanceof Response) {
            return $error;
        }

        History::create([
            'user_id' => $user->id,
            'sender_id' => $userRequest->sender_id,
            'owner_id' => $userRequest->sender_id,
            'gear_id' => $userRequest->gear()->get()->first()->id,
            'event' => 2
        ])->save();

        $userRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request accepted.'
        ]);
    }

    public function giveawayToYourself(Request $request) {
        if (!!($error = UserRequest::giveGearToYourself($request)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear ownership changed.'
        ]);
    }

    public function destroy(Request $request, $id) {
        if (!!($error = UserRequest::deleteRequest($id, $request->user)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully.'
        ]);
    }
}
