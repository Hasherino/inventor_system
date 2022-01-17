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

class HistoryController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index() {
        $history = $this->user->history()->get()->push();
        $senderHistory = History::where('sender_id', $this->user->id)->get();
        foreach($senderHistory as $row) {
            $history = $history->push($row);
        }

        foreach($history as $row) {
            $row['gear'] = $row->gear()->get();
        }

        return $history->sortByDesc('created_at')->values();
    }

    public function gearIndex($id) {
        return Gear::find($id)->history()->get()->sortByDesc('created_at')->values();
    }
}
