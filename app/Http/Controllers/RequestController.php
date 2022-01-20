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
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function pendingRequests() {
        return UserRequest::getUsersPendingRequests($this->user->id)->sortByDesc('created_at')->values();
    }

    public function lend(Request $request) {
        if (!!$error = UserRequest::lendGear($request, $this->user)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Lend request sent.'
        ]);
    }

    public function acceptLend($id) {
        $request = $this->user->request()->find($id);
        $gear = UserRequest::acceptLendGetGear($request);

        if ($gear instanceof Response) {
            return $gear;
        }

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
        if (!!$error = UserRequest::returnGear($request, $this->user->id)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Return request created'
        ]);
    }

    public function acceptReturnLend($id) {
        $request = UserRequest::find($id);
        $gear = UserRequest::acceptReturnGetGear($request, $this->user->id);

        if ($gear instanceof Response) {
            return $gear;
        }

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
        if (!!($error = UserRequest::declineReturn(UserRequest::find($id), $this->user->id)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Return request declined'
        ]);
    }

    public function giveaway(Request $request) {
        if (!!($error = UserRequest::giveawayGear($request, $this->user)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request sent.'
        ]);
    }

    public function acceptGiveaway($id) {
        $request = $this->user->request()->find($id);

        if (!!($error = UserRequest::acceptGiveaway($request, $this->user->id)) instanceof Response) {
            return $error;
        }

        History::create([
            'user_id' => $this->user->id,
            'sender_id' => $request->sender_id,
            'owner_id' => $request->sender_id,
            'gear_id' => $request->gear()->get()->first()->id,
            'event' => 2
        ])->save();

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Giveaway request accepted.'
        ]);
    }

    public function giveawayToYourself(Request $request) {
        if (!!($error = UserRequest::giveGearToYourself($request, $this->user->id)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear ownership changed.'
        ]);
    }

    public function destroy($id) {
        if (!!($error = UserRequest::deleteRequest($id, $this->user)) instanceof Response) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully.'
        ]);
    }
}
