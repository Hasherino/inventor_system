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
    public function index(Request $request) {
        return History::getUsersHistory($request->user)->sortByDesc('created_at')->values();
    }

    public function gearIndex($id) {
        return Gear::find($id)->history()->get()->sortByDesc('created_at')->values();
    }
}
