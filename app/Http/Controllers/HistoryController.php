<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Gear;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class HistoryController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index() {
        $history = $this->user->history()->get()->push();

        $userGear = $this->user->gear()->get();
        foreach($userGear as $gear) {
            $gearHistory = $gear->history()->get();
            if (!$gearHistory->isEmpty()) {
                $history = $history->push($gearHistory);
            }
        }

        return $history->sortBy('created_at')->values();
    }

    public function gearIndex($id) {
        return Gear::find($id)->history()->get();
    }

    public function store(Request $request) {

    }

    public function show($id) {

    }

    public function update(Request $request, $id) {

    }

    public function destroy($id) {

    }
}
