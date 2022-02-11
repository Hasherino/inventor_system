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

        self::createHistory($user->id, $request->sender_id, $gear->get()->first()->user_id, $gear->get()->first()->id, 0);

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
        $userRequest = UserRequest::findOrFail($id);
        $gear = UserRequest::acceptReturnGetGear($userRequest, $request->user->id);

        if ($gear instanceof Response) {
            return $gear;
        }

        self::createHistory($userRequest->sender_id, $gear->user_id, $userRequest->user_id, $gear->id, 1);

        return response()->json([
            'success' => true,
            'message' => 'Gear returned'
        ]);
    }

    public function declineReturnLend(Request $request, $id) {
        if (!!($error = UserRequest::declineReturn(UserRequest::findOrFail($id), $request->user->id)) instanceof Response) {
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

        self::createHistory($user->id, $userRequest->sender_id, $userRequest->sender_id, $userRequest->gear()->get()->first()->id, 2);

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

    private static function createHistory($user_id, $sender_id, $owner_id, $gear_id, $event) {
        History::create([
            'user_id' => $user_id,
            'sender_id' => $sender_id,
            'owner_id' => $owner_id,
            'gear_id' => $gear_id,
            'event' => $event
        ])->save();
    }
}
