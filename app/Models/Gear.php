<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use PDF;

class Gear extends Model
{
    use HasFactory;

    protected $table = 'gear';

    protected $fillable = [
        'name', 'description', 'user_id', 'unit_price', 'code',
        'serial_number', 'long_term', 'lent'
    ];

    public static function getUsersGear($user, $search) {
        $userGear = self::addLentGear($user->gear()->get(), $user);

        $userGear = $userGear->filter(function ($item) use ($search) {
            return false !== stristr($item->name, $search);
        });

        return self::groupByCode($userGear);
    }

    public static function getAllGear($search) {
        return self::groupByCode(gear::where('name', 'ilike', "%$search%")->get());
    }

    public static function createGear($request) {
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

        return $gear;
    }

    public static function getSpecificGear($gear, $id) {
        $gear = $gear->find($id);
        if (!!$error = self::gearCheck($gear)) {
            return $error;
        }

        return $gear;
    }

    public static function updateGear($request, $id) {
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
        if (!!$error = self::gearCheck($gear)) {
            return $error;
        }

        $gear->fill($request->all())->save();

        return $gear;
    }

    public static function deleteGear($request, $user) {
        $data = $request->only('gear_id');
        $validator = Validator::make($data, [
            'gear_id' => 'array|required',
            'gear_id.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $errors = [];
        foreach ($request->gear_id as $id) {
            if ($user->role == 1) {
                $gear = gear::find($id);
            } else {
                $gear = $user->gear->find($id);
            }

            if (!!self::gearCheck($gear)) {
                $errors[] = 'Sorry, gear not found. (id: ' . $id .')';
            } elseif ($gear['lent'] == 1) {
                $errors[] = 'You cannot delete lent gear. (id: ' . $id .')';
            } elseif (!\App\Models\Request::where('gear_id', $gear->id)->get()->isEmpty()) {
                $errors[] = 'Gear has a request. (id: ' . $id .')';
            } else {
                foreach ($gear->history()->get() as $history) {
                    $history->delete();
                }

                $gear->delete();
            }
        }

        if (!!$errors) {
            return response()->json([
                'success' => false,
                'message' => $errors
            ], 400);
        }
    }

    public static function generateGearPDF($id, $user) {
        $gear = Gear::find($id);
        if (!!$error = self::gearCheck($gear)) {
            return $error;
        }

        $request = $user->request()->where('gear_id', $id)->where('status', 1)->orWhere('status', 2)->get()->first();
        if ($gear->user_id != $user->id and !$request) {
            if ($user->role == 0) {
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

    public static function groupByCode($userGear) {
        $userGear = $userGear->groupBy('code')->values();
        $final = [];
        foreach ($userGear as $group) {
            $gear = $group->first();
            $final[] = collect(['name' => $gear->name, 'code' => $gear->code, 'count' => $group->count(),
                'gear' => collect($group)->sortBy('serial_number', SORT_NATURAL|SORT_FLAG_CASE)->values()]);
        }

        return collect($final)->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)->values();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function request() {
        return $this->hasOne(Request::class);
    }

    public function history() {
        return $this->hasMany(History::class);
    }
}
