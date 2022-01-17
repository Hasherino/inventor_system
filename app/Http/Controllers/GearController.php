<?php
namespace App\Http\Controllers;

use App\Models\Gear;
use App\Models\User;
use PDF;
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
        $userGear = $this->user->gear()->get();

        $userGear = $this->addLentGear($userGear, $this->user);

        $search = $request->search;
        $userGear = $userGear->filter(function ($item) use ($search) {
            return false !== stristr($item->name, $search);
        });

        return $this->groupByCode($userGear);
    }

    public function index(Request $request) {
        return $this->groupByCode(gear::where('name', 'ilike', "%$request->search%")->get());
    }

    public function selectedIndex(Request $request, $id) {
        $selectedUser = User::find($id);
        if(!$selectedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user not found.'
            ], 404);
        }

        $userGear = $selectedUser->gear()->get();

        $userGear = $this->addLentGear($userGear, $this->user);

        $search = $request->search;
        $userGear = $userGear->filter(function ($item) use ($search) {
            return false !== stristr($item->name, $search);
        });

        return $this->groupByCode($userGear);
    }

    public function store(Request $request) {
        $data = $request->only('name', 'description', 'code', 'serial_number', 'unit_price', 'long_term', 'user_id', 'amount');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'code' => 'required|string',
            'description' => 'string|max:255',
            'serial_number' => 'string|unique:gear',
            'unit_price' => 'required|numeric',
            'long_term' => 'required|boolean',
            'user_id' => 'required|integer',
            'amount' => 'required|integer|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        for ($i = 0; $i < $request->amount; $i++) {
            $sameGear = Gear::where('code', $request->code)->get()->first();

            if(!!$sameGear and ($sameGear->name != $request->name or
                                $sameGear->description != $request->description or
                                $sameGear->unit_price != $request->unit_price)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gear does not match with other ones with the same code',
                ], 400);
            }
            $gear = Gear::create($request->all())->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Gear added successfully',
            'data' => $gear
        ], 201);
    }

    public function show($id) {
        $gear = Gear::find($id);
        if (!!$error = $this->gearCheck($gear)) {
            return $error;
        }

        return $gear;
    }

    public function showByCode($code) {
        $gear = Gear::where('code', $code)->get()->first();
        if (!!$error = $this->gearCheck($gear)) {
            return $error;
        }

        return $gear;
    }

    public function userShow($id) {
        $userGear = $this->user->gear()->get();
        $userGear = $this->addLentGear($userGear, $this->user);

        $selectedGear = $userGear->find($id);
        if (!!$error = $this->gearCheck($selectedGear)) {
            return $error;
        }

        return $selectedGear;
    }

    public function update(Request $request, $id) {
        $data = $request->only('name', 'code', 'description', 'serial_number', 'unit_price', 'long_term', 'user_id');
        $validator = Validator::make($data, [
            'name' => 'string',
            'code' => 'string',
            'description' => 'string',
            'serial_number' => 'string',
            'unit_price' => 'numeric',
            'long_term' => 'boolean',
            'user_id' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $gear = gear::find($id);
        if (!!$error = $this->gearCheck($gear)) {
            return $error;
        }

        $gear->fill($request->all());
        $gear->save();

        return response()->json([
            'success' => true,
            'message' => 'Gear updated successfully',
            'data' => $gear
        ]);
    }

    public function destroy($id) {
        if ($this->user->role == 1) {
            $gear = gear::find($id);
        } else {
            $gear = $this->user->gear->find($id);
        }

        if (!!$error = $this->gearCheck($gear)) {
            return $error;
        }

        if ($gear['lent'] == 1) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete lent gear'
            ], 400);
        }

        if (!\App\Models\Request::where('gear_id', $gear->id)->get()->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Gear has a request'
            ], 400);
        }

        foreach($gear->history()->get() as $history) {
            $history->delete();
        }

        $gear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gear deleted successfully'
        ]);
    }

    public function generatePDF($id) {
        $gear = Gear::find($id);
        if (!!$error = $this->gearCheck($gear)) {
            return $error;
        }
        $request = $this->user->request()->where('gear_id', $id)->
                   where('status', 1)->orWhere('status', 2)->get()->first();
        if ($gear->user_id != $this->user->id and !$request) {
            if ($this->user->role == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authorized'
                ], 401);
            }
        }

        $user = $gear->user()->get()->first();
        $gear['owner'] = $user->first_name.' '.$user->last_name;

        $history = $gear->history()->get()->sortByDesc('created_at');
        foreach ($history as $row) {
            $owner = User::find($row->owner_id);
            $sender = User::find($row->sender_id);
            $user = User::find($row->user_id);
            $row['owner'] = $owner->first_name.' '.$owner->last_name;
            $row['sender'] = $sender->first_name.' '.$sender->last_name;
            $row['user'] = $user->first_name.' '.$user->last_name;
        }

        $data['gear'] = $gear;
        $data['history'] = $history;

        $pdf = PDF::loadView('pdf', $data);

        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf ->get_canvas();
        $canvas->page_text(270, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));

        return $pdf->download('pdf_file.pdf');
    }

    public function groupByCode($userGear) {
        $userGear = $userGear->groupBy('code')->values();
        $final = [];
        foreach ($userGear as $group) {
            $gear = $group->first();
            $final[] = collect(['name' => $gear->name, 'code' => $gear->code, 'count' => $group->count(),
                                'gear' => collect($group)]);
        }

        return $final;
    }
}
