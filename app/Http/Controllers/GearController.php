<?php
namespace App\Http\Controllers;

use App\Models\Gear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class GearController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function userIndex(Request $request) {
        return Gear::getUsersGear($this->user, $request->search);
    }

    public function index(Request $request) {
        return Gear::getAllGear($request->search);
    }

    public function selectedIndex(Request $request, $id) {
        $selectedUser = User::find($id);
        if(!$selectedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        return Gear::getUsersGear($selectedUser, $request->search);
    }

    public function store(Request $request) {
        $gear = Gear::createGear($request);

        if ($gear instanceof Response) {
            return $gear;
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear added successfully',
            'data' => $gear
        ], 201);
    }

    public function show($id) {
        return Gear::getSpecificGear(Gear::all(), $id);
    }

    public function showByCode($code) {
        return Gear::getSpecificGear(Gear::all(), Gear::where('code', $code)->get()->first()->id);
    }

    public function userShow($id) {
        return Gear::getSpecificGear(Gear::addLentGear($this->user->gear()->get(), $this->user), $id);
    }

    public function update(Request $request, $id) {
        $gear = Gear::updateGear($request, $id);

        if ($gear instanceof Response) {
            return $gear;
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear updated successfully',
            'data' => $gear
        ]);
    }

    public function destroy(Request $request) {
        if (!!$error = Gear::deleteGear($request, $this->user)) {
            return $error;
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear deleted successfully'
        ]);
    }

    public function generatePDF($id) {
        return Gear::generateGearPDF($id, $this->user);
    }
}
